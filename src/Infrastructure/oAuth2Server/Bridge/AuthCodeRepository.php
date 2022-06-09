<?php

namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Entity\AuthCode as AppAuthCode;
use App\Repository\AuthCodeRepository as AppAuthCodeRepository;
use App\Repository\ClientRepository as AppClientRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /** @var AppAuthCodeRepository */
    private $authCodeRepository;

    public function __construct(AppAuthCodeRepository $authCodeRepository, AppClientRepository $clientRepository)
    {
        $this->authCodeRepository = $authCodeRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $client = $this->clientRepository->find($authCodeEntity->getClient()->getIdentifier());

        $authCode = new AppAuthCode(
            $authCodeEntity->getIdentifier(),
            $client,
            $authCodeEntity->getExpiryDateTime(),
            $authCodeEntity->getUserIdentifier()
        );

        $this->authCodeRepository->save($authCode);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {

    }
}
