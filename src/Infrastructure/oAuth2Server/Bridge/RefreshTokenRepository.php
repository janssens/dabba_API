<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Entity\RefreshToken as AppRefreshToken;
use App\Repository\ClientRepository as AppClientRepository;
use App\Repository\RefreshTokenRepository as AppRefreshTokenRepository;
use \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use \League\OAuth2\Server\Entities\RefreshTokenEntityInterface;


final class RefreshTokenRepository implements RefreshTokenRepositoryInterface{

    /** @var AppRefreshTokenRepository */
    private $appRefreshTokenRepository;

    /** @var AccessTokenRepository */
    private $accessTokenRepository;

    public function __construct(AppRefreshTokenRepository $refreshTokenRepository,AccessTokenRepository $accessTokenRepository){
        $this->appRefreshTokenRepository = $refreshTokenRepository;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $id = $refreshTokenEntity->getIdentifier();
        $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier();
        $expiryDateTime = $refreshTokenEntity->getExpiryDateTime();

        $refreshTokenPersistEntity = new AppRefreshToken($id, $accessTokenId, $expiryDateTime);
        $this->appRefreshTokenRepository->save($refreshTokenPersistEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId): void
    {
        $refreshTokenPersistEntity = $this->appRefreshTokenRepository->find($tokenId);
        if ($refreshTokenPersistEntity === null) {
            return;
        }
        $refreshTokenPersistEntity->revoke();
        $this->appRefreshTokenRepository->save($refreshTokenPersistEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshTokenPersistEntity = $this->appRefreshTokenRepository->find($tokenId);
        if ($refreshTokenPersistEntity === null || $refreshTokenPersistEntity->isRevoked()) {
            return true;
        }
        return $this->accessTokenRepository->isAccessTokenRevoked($refreshTokenPersistEntity->getAccessTokenId());
    }
}