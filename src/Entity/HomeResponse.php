<?php

namespace App\Entity;
use Nelmio\ApiDocBundle\Annotation\Model;

class HomeResponse{

    /**
     */
    private $cms;

    /**
     */
    private $user;

    /**
     */
    private $stats;

    public function __construct($cms,$user,$stats){
        $this->cms = $cms;
        $this->user = $user;
        $this->stats = $stats;
    }
}
