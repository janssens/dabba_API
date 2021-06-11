<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Repository\UserRepository as AppUserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserRepository implements UserRepositoryInterface
{
    /** @var AppUserRepository */
    private $appUserRepository;

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    public function __construct(AppUserRepository $userRepository,UserPasswordEncoderInterface $userPasswordEncoder){
        $this->appUserRepository = $userRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $appUser = $this->appUserRepository->findOneByEmail($username);
        if ($appUser === null) {
            return null;
        }
        $isPasswordValid = $this->userPasswordEncoder->isPasswordValid($appUser, $password);
        if (!$isPasswordValid) {
            return null;
        }
        $oAuthUser = new User($appUser->getId());
        return $oAuthUser;
    }
}