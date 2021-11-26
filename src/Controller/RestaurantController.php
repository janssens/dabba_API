<?php

namespace App\Controller;

use App\Entity\CodeRestaurant;
use App\Entity\Restaurant;
use App\Entity\Zone;
use App\Service\Place;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RestaurantController
 * @package App\Controller
 * @Route("/restaurant")
 */
class RestaurantController extends AbstractController
{
    /**
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     *
     * @var Place
     */
    private $place;

    public function __construct(EntityManagerInterface $em,Place $place)
    {
        $this->em = $em;
        $this->place = $place;
    }

    /**
     * @Route("/qr/{code}",name="app_restaurant_qr")
     */
    public function register(CodeRestaurant $codeRestaurant): Response
    {
        return $this->render('restaurant/qr.html.twig', [
            'restaurant' => $codeRestaurant->getRestaurant(),
            'code' => $codeRestaurant,
        ]);
    }

    /**
     * @Route("/import",name="restaurant_import")
     */
    public function import(Request $request): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')){
            throw new AccessDeniedException();
        }
        $form = $this->createFormBuilder(null)
            ->add('zone',EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'name'
            ])
            ->add('restaurants', TextareaType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'données des restaurants à importer au format JSON'
            ])
            ->add('save', SubmitType::class, ['label' => 'Import'])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            if (!$form->isValid()) {
                throw new AccessDeniedException();
            }
            $zone = $form->getData()['zone'];
            $raw = $form->getData()['restaurants'];
            $restaurants = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new HttpException(400, 'Invalid json : '.json_last_error_msg());
            }
            $return = ['info'=>[],'error'=>[]];
            foreach ($restaurants as $restaurant) {
                if (!isset($restaurant['nom'])){
                    $return['error'][] = 'Missing \'nom\' property for '.json_encode($restaurant);
                    continue;
                }
                if (!isset($restaurant['adresse'])){
                    $return['error'][] = 'Missing \'adresse\' property for '.json_encode($restaurant);
                    continue;
                }
                $name = strtolower($restaurant['nom']);
                $address = $restaurant['adresse'];
                if ($name&$address){
                    $exist = $this->em->getRepository(Restaurant::class)->findOneBy(array('name'=>$name));
                    if (!$exist){
                        $data = $this->place->search($name,$address);
                        if (isset($data['error'])){
                            $return['error'][] = $data['error'] . 'for "'.$name.'" and "'.$address.'"';
                        }else{
                            if (count($data['success'])>1){
                                $return['error'][] = 'Not only one result in Google Place ' . 'for "'.$name.'" and "'.$address.'"';
                            }else{
                                $found = $data['success'][0];
                                $restaurant = new Restaurant();
                                $restaurant->setName($name);
                                $restaurant->setLat($found['geometry']['location']['lat']);
                                $restaurant->setLng($found['geometry']['location']['lng']);
                                $restaurant->setFormattedAddress($found['formatted_address']);
                                $restaurant->setGooglePlaceId($found['place_id']);
                                $details = $this->place->getDetails($found['place_id']);
                                if (isset($details['success'])){
                                    if (isset($details['success']['opening_hours'])) {
                                        $restaurant->setOpeningHours($details['success']['opening_hours']['weekday_text']);
                                    }
                                    if (isset($details['success']['website'])){
                                        $restaurant->setWebsite($details['success']['website']);
                                    }
                                    if (isset($details['success']['formatted_phone_number'])) {
                                        $restaurant->setPhone($details['success']['formatted_phone_number']);
                                    }
                                }
                                $restaurant->setZone($zone);
                                $this->em->persist($restaurant);
                                $return['info'][] = '<strong>'.$restaurant->getName(). ' </strong> successfully created';
                            }
                        }
                    }else{
                        $return['error'][] = 'a Restaurant with name "'.$name.'" already exist. skip this line.';
                    }
                }else{
                    $return['error'][] = 'Missing data for "'.$name.'" "'.$address.'".';
                }
            }
            $this->em->flush();
            return $this->render('restaurant/import.html.twig',['form'=>$form->createView(),'return'=>$return]);
        }

        return $this->render('restaurant/import.html.twig',['form'=>$form->createView()]);
    }
}
