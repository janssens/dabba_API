<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Repository\ClientRepository as AppClientRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

final class ClientRepository implements ClientRepositoryInterface
{

    /** @var AppClientRepository */
    private $appClientRepository;

    public function __construct(AppClientRepository $clientRepository){
        $this->appClientRepository = $clientRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = false
    ): ?ClientEntityInterface {
        $appClient = $this->appClientRepository->findActive($clientIdentifier);
        if ($appClient === null) {
            return null;
        }
        if ($mustValidateSecret && !hash_equals($appClient->getSecret(), (string)$clientSecret)) {
            return null;
        }
        $oauthClient = new Client($clientIdentifier, $appClient->getName(), $appClient->getRedirect());
        return $oauthClient;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        // TODO: Implement validateClient() method.
    }
}