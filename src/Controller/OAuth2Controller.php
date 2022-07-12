<?php

namespace App\Controller;

use App\Infrastructure\oAuth2Server\Bridge\User;
use App\Entity\Order;
use App\Entity\Zone;
use App\Form\AuthorizeForm;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\SystemPay;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Diactoros\Response as Psr7Response;
use Zend\Diactoros\Stream;
use ApiPlatform\Core\Documentation\Documentation;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

/**
 * @Route("/oauth2")
 */
final class OAuth2Controller extends AbstractController
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var PasswordGrant
     */
    private $passwordGrant;

    /**
     * @var RefreshTokenGrant
     */
    private $refreshTokenGrant;

    /**
     * AuthController constructor.
     * @param AuthorizationServer $authorizationServer
     * @param PasswordGrant $passwordGrant
     * @param RefreshTokenGrant $refreshTokenGrant
     */
    public function __construct(
        AuthorizationServer $authorizationServer,
        AuthCodeGrant $authCodeGrant,
        SystemPay $systemPay)
    {
        $authCodeGrant->disableRequireCodeChallengeForPublicClients();

        $authorizationServer->enableGrantType(
            $authCodeGrant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        $this->authorizationServer = $authorizationServer;
        $this->systemPay = $systemPay;
    }

    /**
     * @Route("/authorize", name="oauth2_authorize", methods={"GET", "POST"})
     */
    public function authorize(Request $request,
        HttpMessageFactoryInterface $psrHttpFactory,
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $loginAuthenticator,
        GuardAuthenticatorHandler $guard)
    {
        $response = new Psr7Response();

        $form = $this->createForm(AuthorizeForm::class);
        $form->handleRequest($request);

        // https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->authorizationServer->validateAuthorizationRequest(
                $psrHttpFactory->createRequest($request)
            );

            $user = $this->getUser();

            if (null === $user) {

                $registrationForm = $this->createForm(RegistrationFormType::class);
                $registrationForm->handleRequest($request);

                if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {

                    $user = $registrationForm->getData();

                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $registrationForm->get('plainPassword')->getData()
                        )
                    );
                    $user->setIsVerified(true);

                    $entityManager = $this->getDoctrine()->getManager();

                    if (!$user->getZone())
                        $user->setZone($entityManager->getRepository(Zone::class)->findDefault());

                    $entityManager->persist($user);
                    $entityManager->flush();

                    // https://ourcodeworld.com/articles/read/1490/how-to-manually-sign-in-a-registered-user-in-symfony-5
                    // https://stackoverflow.com/questions/66924935/how-to-manually-authenticate-user-after-registration-with-the-new-symfony-5-auth

                    $guard->authenticateUserAndHandleSuccess(
                        $user,
                        $request,
                        $loginAuthenticator,
                        'main'
                    );

                    return $this->redirectToRoute('oauth2_authorize', $request->query->all());
                }

                return $this->render('oauth2/login_register.html.twig', [
                    'client_name' => $authRequest->getClient()->getName(),
                    'last_username' => '',
                    'error' => '',
                    'registration_form' => $registrationForm->createView(),
                ]);
            }

            if ($request->query->has('expected_wallet')) {

                // https://paiement.systempay.fr/doc/fr-FR/rest/V4.0/javascript/guide/payment_form.html
                // https://paiement.systempay.fr/doc/fr-FR/rest/V4.0/kb/payment_done.html
                // https://paiement.systempay.fr/doc/fr-FR/rest/V4.0/javascript/spa/

                $currentWallet = $user->getWallet();
                $expectedAmount = $request->query->get('expected_wallet');
                $amount = $expectedAmount - $currentWallet;

                if ($amount > 0) {

                    $order = new Order();
                    $order->setAmount($amount);
                    $order->setUser($user);
                    $order->setState(Order::STATE_NEW);
                    $order->setStatus(Order::STATUS_NEW);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($order);
                    $em->flush();

                    return $this->render('oauth2/wallet.html.twig', [
                        'public_key' => $this->systemPay->getPublicKey(),
                        'form_token' => $this->systemPay->getTokenForOrder($order),
                    ]);
                }
            }

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser(new User($this->getUser()->getId())); // an instance of UserEntityInterface

            if ($form->isSubmitted() && $form->isValid()) {

                // Once the user has approved or denied the client update the status
                // (true = approved, false = denied)
                $authRequest->setAuthorizationApproved(
                    $form->get('approve')->isClicked()
                );

                // Return the HTTP redirect response
                return $this->authorizationServer->completeAuthorizationRequest($authRequest, $response);
            }

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            return $this->render('oauth2/authorize.html.twig', [
                'client_name' => $authRequest->getClient()->getName(),
                'form' => $form->createView(),
            ]);

        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);

        } catch (\Exception $exception) {

            // Unknown exception
            $body = new Stream(fopen('php://temp', 'r+'));
            $body->write($exception->getMessage());

            return $response->withStatus(500)->withBody($body);
        }
    }

    /**
     * @Route("/token", name="oauth2_token", methods={"POST"})
     */
    public function token(ServerRequestInterface $request): ?Psr7Response
    {
        $response = new Psr7Response();

        try {

            // Try to respond to the request
            return $this->authorizationServer->respondToAccessTokenRequest($request, $response);

        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);

        } catch (\Exception $exception) {

            // Unknown exception
            $body = new Stream(fopen('php://temp', 'r+'));
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);
        }
    }
}
