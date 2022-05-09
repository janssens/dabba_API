<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\Restaurant;
use App\Entity\User;

use App\Repository\AccessTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /** @var UserPasswordEncoderInterface  */
    private $passwordEncoder;
    /** @var AccessTokenRepository  */
    private $accessTokentRepo;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,AccessTokenRepository $accessTokenRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->accessTokentRepo = $accessTokenRepository;
    }

    /**
     * @Route(
     *     "/api/users/current",
     *     name="api_current_user",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = User::class,
     *          "_api_collection_operation_name" = "current_user",
     *     })
     */
    public function getCurrentUser(): User
    {
        return $this->getUser();
    }

    /**
     * @Route(
     *     "/api/users/restaurants/add/{id}",
     *     name="api_add_to_favorite",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = User::class,
     *          "_api_collection_operation_name" = "add_to_favorite",
     *     })
     */
    public function addToFavorite(Restaurant $restaurant): User
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $current_user */
        $current_user = $this->getUser();
        $current_user->addRestaurant($restaurant);
        $em->persist($current_user);
        $em->flush();
        return $current_user;
    }

    /**
     * @Route(
     *     "/api/users/restaurants/remove/{id}",
     *     name="api_remove_from_favorite",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = User::class,
     *          "_api_collection_operation_name" = "remove_from_favorite",
     *     })
     */
    public function removeFromFavorite(Restaurant $restaurant): User
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $current_user */
        $current_user = $this->getUser();
        $current_user->removeRestaurant($restaurant);
        $em->persist($current_user);
        $em->flush();
        return $current_user;
    }

    /**
     * @param Request $request
     * @Route("/api/users/forget_me",name="api_user_forget")
     */
    public function forgetMe(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user){
            return $this->json(['message'=>'Please login','code'=>503],503);
        }

        $password = $request->get('password');

        if (!$password){
            return $this->json(['message'=>'Please provide the user current password','code'=>400],400);
        }

        $test = $this->passwordEncoder->isPasswordValid($user,$password);

        if ($test){
            $user->eraseCredentials();
            $user->setIsVerified(false);
            $user->setFirstname('');
            $user->setLastName('');
            $user->setEmail('anonymous_'.date('YmdHis').'@localhost');
            $user->setDob(null);
            $user->setRoles(['ROLE_ANONYMOUS']);
            $em->persist($user);
            foreach ($this->accessTokentRepo->findAllActiveForUser($user) as $token){
                $token->revoke();
                $em->persist($token);
            }
            $em->flush();
        }else{
            throw new AccessDeniedException();
        }
        return $this->json([],200);
    }

}

