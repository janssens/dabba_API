<?php

namespace App\Controller;

use App\Infrastructure\oAuth2Server\Bridge\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Diactoros\Response as Psr7Response;
use Zend\Diactoros\Stream;
use ApiPlatform\Core\Documentation\Documentation;

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
        AuthCodeGrant $authCodeGrant)
    {
        $authCodeGrant->disableRequireCodeChallengeForPublicClients();

        $authorizationServer->enableGrantType(
            $authCodeGrant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        $this->authorizationServer = $authorizationServer;
    }

    /**
     * @Route("/authorize", name="oauth2_authorize", methods={"GET"})
     */
    public function authorize(ServerRequestInterface $request): ?Psr7Response
    {
        $response = new Psr7Response();

        // https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser(new User($this->getUser()->getEmail())); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $this->authorizationServer->completeAuthorizationRequest($authRequest, $response);

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
