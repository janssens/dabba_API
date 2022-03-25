<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodeRestaurant;
use App\Entity\Container;
use App\Entity\Movement;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Entity\Zone;
use App\Form\ZoneType;
use App\Service\Place;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use function PHPUnit\Framework\throwException;

class RestaurantCrudController extends AbstractCrudController
{
    private $adminUrlGenerator;
    private $place;

    public function __construct(AdminUrlGenerator $adminUrlGenerator,Place $place)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->place = $place;
    }

    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('tags')
            ->add('zone')
            ->add('mealTypes')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $actions
            ->addBatchAction(Action::new('createMissingQr', 'créer un QR si manquant')
                ->linkToCrudAction('createMissingQr')
                ->addCssClass('btn btn-secondary')
                ->setIcon('fa fa-qrcode'));
        $actions
            ->addBatchAction(Action::new('createMissingPlaceId', 'trouver Google Place Id si manquant')
                ->linkToCrudAction('createMissingPlaceId')
                ->addCssClass('btn btn-secondary')
                ->setIcon('fa fa-map-marker'));
        $inventory = Action::new('inventory', 'Inventaire', 'fa fa-box')
            ->linkToCrudAction('inventory');
        $actions->add(Crud::PAGE_INDEX, $inventory);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            ImageField::new('image')
                ->setBasePath($this->getParameter('app.path.restaurant_images'))
                ->setUploadDir('/public'.$this->getParameter('app.path.restaurant_images'))
                ->setUploadedFileNamePattern("[year][month][contenthash]-[slug].[extension]"),
            TextField::new('name','nom'),
            TextField::new('formatted_address','adresse'),
            AssociationField::new('zone')->hideOnForm(),
            TextField::new('stockAsText','Stock')->hideOnForm(),
            BooleanField::new('featured','Mis en avant'),
            BooleanField::new('show_on_map','Afficher sur la carte'),
            BooleanField::new('show_on_map','visible sur la carte')->onlyWhenUpdating(),
            TelephoneField::new('phone','telephone')->hideOnIndex(),
            BooleanField::new('hasValidCode','Qr code valide')->onlyOnIndex(),
            TextField::new('google_place_id','Google Place ID ')->onlyOnIndex(),
            TextField::new('website')->onlyWhenUpdating(),
            AssociationField::new('tags')->hideOnIndex(),
            AssociationField::new('mealTypes')->hideOnIndex()
        ];

        if ($this->isGranted('ROLE_SUPER_ADMIN')){
            $fields[] = AssociationField::new('zone')->onlyWhenUpdating();
        }

        return $fields;
    }

    public function createMissingQr(BatchActionDto $batchActionDto)
    {
        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var Restaurant $restaurant */
            $restaurant = $entityManager->find(Restaurant::class,$id);
            if (!$restaurant->hasValidCode()){
                $qr = new CodeRestaurant();
                $qr->setRestaurant($restaurant);
                $qr->setEnabled(true);
                $entityManager->persist($qr);
            }
        }
        $entityManager->flush();
        return $this->redirect($batchActionDto->getReferrerUrl());
    }

    public function createMissingPlaceId(BatchActionDto $batchActionDto)
    {
        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var Restaurant $restaurant */
            $restaurant = $entityManager->find(Restaurant::class,$id);
            if (!$restaurant->getGooglePlaceId()){
                $data = $this->place->search($restaurant->getName(),$restaurant->getFormattedAddress());
                if (!isset($data['error'])){
                    if (count($data['success'])>1){
                        throw new \Exception('Not only one result');
                    }else{
                        $found = $data['success'][0];
                        $restaurant->setLat($found['geometry']['location']['lat']);
                        $restaurant->setLng($found['geometry']['location']['lng']);
                        $restaurant->setFormattedAddress($found['formatted_address']);
                        $restaurant->setGooglePlaceId($found['place_id']);

                        $exist = $entityManager->getRepository(Restaurant::class)->findOneBy(['google_place_id'=>$restaurant->getGooglePlaceId()]);
                        /** @var Restaurant $exist */
                        if ($exist){
                            throw new \Exception('A restaurant with the same google place id already exist : #'.$exist->getId().' '.$exist->getName().' (google place id = "'.$exist->getGooglePlaceId().'")');
                        }
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
                    }
                }else{
                    throw new \Exception($data['error']);
                }
                $entityManager->persist($restaurant);
            }
        }
        $entityManager->flush();
        return $this->redirect($batchActionDto->getReferrerUrl());
    }

    public function inventory(AdminContext $context,Request $request)
    {
        /** @var Restaurant $restaurant */
        $restaurant = $context->getEntity()->getInstance();
        $allow_negative_stock = $this->getParameter('app.allow_negative_stock');
        $em = $this->getDoctrine()->getManager();
        $containers = $em->getRepository(Container::class)->findAll();
        $stocks = $restaurant->getContainers();

        $formBuilder = $this->createFormBuilder()
            ->add('qty', IntegerType::class, ['label' => 'Nouvelle quantité']);
        if (count($containers)>1){
            $formBuilder->add('container_id',EntityType::class, ['class' => Container::class]);
        }else if (isset($containers[0])){
            $formBuilder->add('container_id',HiddenType::class, [ 'data' => $containers[0]->getId()]);
        }
        $formBuilder->add('save', SubmitType::class, ['label' => 'créer mouvement d\'inventaire']);
        $inventory_form = $formBuilder->getForm();
        $inventory_form->handleRequest($request);
        if ($inventory_form->isSubmitted() && $inventory_form->isValid()) {
            $qty = $inventory_form->get('qty')->getData();
            if ($qty<0 && !$allow_negative_stock){
                throw new \Exception('negative stock not allowed');
            }
            $container_id = $inventory_form->get('container_id')->getData();
            $container = $em->getRepository(Container::class)->find($container_id);
            if (!$container){
                throw new \Exception('container not found');
            }
            if (!$restaurant->getStock()){
                $stock = new Stock();
                $stock->setType(Stock::TYPE_RESTAURANT);
                $stock->setRestaurant($restaurant);
                $em->persist($stock);
                $restaurant->setStock($stock);
            }
            if (key_exists($container_id,$stocks)){
                $to_move = $qty - $stocks[$container_id];
            }else{
                $to_move = $qty;
            }
            $move = new Movement();
            $move->setContainer($container);
            if ($to_move > 0){
                $move->setStockFrom(null);
                $move->setStockTo($restaurant->getStock());
            }else{
                $move->setStockFrom($restaurant->getStock());
                $move->setStockTo(null);
            }
            $move->setQuantity(abs($to_move));
            $move->setReason(Movement::TYPE_INVENTORY);
            $em->persist($move);
            $em->flush();

            $url = $this->adminUrlGenerator
                ->setController(RestaurantCrudController::class)
                ->setAction('inventory')
                ->generateUrl();

            return $this->redirect($url);
        }

        $formBuilder2 = $this->createFormBuilder()
            ->add('qty', IntegerType::class, ['label' => 'Quantité']);
        if (count($containers)>1){
            $formBuilder2->add('container_id',EntityType::class, ['class' => Container::class]);
        }else if (isset($containers[0])){
            $formBuilder2->add('container_id',HiddenType::class, [ 'data' => $containers[0]->getId()]);
        }
        $formBuilder2->add('type', ChoiceType::class, [
            'label' => 'raison',
            'choices'  => [
                'Perte' => Movement::TYPE_LOST,
                'Casse' => Movement::TYPE_BROKEN,
                'Ajout Logistique' => -1*Movement::TYPE_LOGISTICS,
                'Retrait Logistique' => Movement::TYPE_LOGISTICS
            ],
        ]);
        $formBuilder2->add('save', SubmitType::class, ['label' => 'créer mouvement de correction']);
        $fix_form = $formBuilder2->getForm();
        $fix_form->handleRequest($request);
        if ($fix_form->isSubmitted() && $fix_form->isValid()) {
            $qty = $fix_form->get('qty')->getData();
            $container_id = $fix_form->get('container_id')->getData();
            $type = $fix_form->get('type')->getData();
            $container = $em->getRepository(Container::class)->find($container_id);
            if (!$container){
                throw new \Exception('container not found');
            }
            if (($type > 0)&&($qty>$stocks[$container_id])&&!$allow_negative_stock){
                throw new \Exception('negative stock not allowed');
            }
            if (!$restaurant->getStock()){
                $stock = new Stock();
                $stock->setType(Stock::TYPE_RESTAURANT);
                $stock->setRestaurant($restaurant);
                $em->persist($stock);
                $restaurant->setStock($stock);
            }
            $move = new Movement();
            $move->setContainer($container);
            if ($type < 0){
                $move->setStockFrom(null);
                $move->setStockTo($restaurant->getStock());
            }else{
                $move->setStockFrom($restaurant->getStock());
                $move->setStockTo(null);
            }
            $move->setQuantity(abs($qty));
            $move->setReason(abs($type));
            $em->persist($move);
            $em->flush();

            $url = $this->adminUrlGenerator
                ->setController(RestaurantCrudController::class)
                //->setAction(Action::INDEX)
                ->setAction('inventory')
                ->generateUrl();

            return $this->redirect($url);
        }

        $last_movements = $em->getRepository(Movement::class)->findLastForRestaurant($restaurant, 10);
        return $this->render('admin/crud/inventory.html.twig',
            [
                'restaurant'=>$restaurant,
                'inventory_form' => $inventory_form->createView(),
                'fix_form' => $fix_form->createView(),
                'last_movements' => $last_movements
            ]);
    }

}
