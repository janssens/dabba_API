<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Repository\AccessTokenRepository as AppAccessTokenRepository;
use App\Entity\AccessToken as AppAccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    /** @var AppAccessTokenRepository */
    private $appAccessTokenRepository;

    public function __construct(AppAccessTokenRepository $accessTokenRepository){
        $this->appAccessTokenRepository = $accessTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        $at = new AccessToken($userIdentifier, $scopes);
        $at->setClient($clientEntity);
        return $at;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $appAccessToken = new AppAccessToken(
            $accessTokenEntity->getIdentifier(),
            $accessTokenEntity->getUserIdentifier(),
            $accessTokenEntity->getClient()->getIdentifier(),
            $this->scopesToArray($accessTokenEntity->getScopes()),
            false,
            new \DateTime(),
            new \DateTime(),
            $accessTokenEntity->getExpiryDateTime()
        );
        $this->appAccessTokenRepository->save($appAccessToken);
    }

    private function scopesToArray(array $scopes): array
    {
        return array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId): void
    {
        $appAccessToken = $this->appAccessTokenRepository->find($tokenId);
        if ($appAccessToken === null) {
            return;
        }
        $appAccessToken->revoke();
        $this->appAccessTokenRepository->save($appAccessToken);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId): ?bool
    {
        $appAccessToken = $this->appAccessTokenRepository->find($tokenId);
        if ($appAccessToken === null) {
            return true;
        }
        return $appAccessToken->isRevoked();
    }
}