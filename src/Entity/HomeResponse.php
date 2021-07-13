<?php

namespace App\Entity;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class HomeResponse{

    /**
     * @OA\Property(
     *     description="list of cms block",
     *     type="array",
     *     @OA\Items(ref=@Model(type=Cms::class))
     * )
     */
    private $cms;

    /**
     * @OA\Property(
     *     description="The current user",
     *     ref=@Model(type=User::class)
     * )
     */
    private $user;

    /**
     * @OA\Property(
     *     description="Statistics for display",
     *     type="array",
     *     @OA\Items(type="string")
     * )
     */
    private $stats;

    public function __construct($cms,$user,$stats){
        $this->cms = $cms;
        $this->user = $user;
        $this->stats = $stats;
    }
}
