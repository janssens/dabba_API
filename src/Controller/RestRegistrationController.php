<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Infrastructure\oAuth2Server\Bridge\ClientRepository;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RestRegistrationController extends AbstractFOSRestController
{

    private $emailVerifier;
    private $_clientRepository;

    public function __construct(EmailVerifier $emailVerifier,ClientRepository $clientRepository)
    {
        $this->emailVerifier = $emailVerifier;
        $this->_clientRepository = $clientRepository;
    }

    /**
     * @Rest\Post(
     *    path = "/api/register",
     *    name = "api_register"
     * )
     * @Rest\View(StatusCode = 201)
     * @Route("/api/register", name="api_register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @throws \Exception
     */
    public function createAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $client = $this->_clientRepository->getClientEntity($request->request->get('client_id'),null,$request->request->get('client_secret'),true);

        if (!$client){
            return [ 'success' => false, 'errors' => 'you should provide a valid client id and secret'];
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, array('csrf_protection' => false));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('dabba@plopcom.fr', 'Dabba consigne'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return [ 'success' => false, 'user' => $user];
        }else{
            $errors = $form->getErrors(true,true);
            if (count($errors)){
                $a_errors = [];
                /** @var FormError $error */
                foreach ($errors as $error) {
                    $a_errors[] = $error->getMessage();
                }
                return [ 'success' => false, 'errors' => $a_errors];
            }
        }

    }
}
