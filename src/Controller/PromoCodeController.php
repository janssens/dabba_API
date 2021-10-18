<?php

namespace App\Controller;

use App\Entity\Cms;
use App\Entity\CodePromo;
use App\Entity\HomeResponse;
use App\Entity\Movement;
use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Zone;
use App\Exception\AlreadyPaid;
use App\Exception\AlreadyUsed;
use App\Exception\Disabled;
use App\Exception\Expired;
use App\Exception\NotFound;
use App\Service\SystemPay;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use http\Header;
use PhpParser\Node\Expr\Array_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PromoCodeController extends AbstractController
{


    /**
     * @Route(
     *     "/api/code_promos/apply",
     *     name="api_apply_code_promo",
     *     methods={"POST"},
     *     defaults={
     *          "_api_resource_class" = CodePromo::class,
     *          "_api_collection_operation_name" = "promocode_apply",
     *     })
     */
    public function applyPromoCode(Request $request): array
    {
        $content = $request->getContent();
        if (empty($content)) {
            throw new NotFound("No code posted");
        }
        $params = json_decode($content, true); // 2nd param to get as array
        if (!isset($params['code'])||!($promocode=$params['code'])){
            throw new NotFound("No code posted");
        }
        $em = $this->getDoctrine()->getManager();
        /** @var CodePromo $codePromo */
        $codePromo = $em->getRepository(CodePromo::class)->findOneBy(array('code'=>$promocode));
        if (!$codePromo){
            throw new NotFound("No item found with this code");
        }
        if (!$codePromo->getEnabled()){
            throw new Disabled('This code is disabled');
        }
        $now = new \DateTime('now');
        if ($now > $codePromo->getExpiredAt()){
            throw new Expired('This code is expired');
        }
        if ($codePromo->getUsedAt() || $codePromo->getUsedBy()){
            throw new AlreadyUsed('This code was already used');
        }

        $codePromo->setUsedAt($now);
        $codePromo->setUsedBy($this->getUser());

        $em->persist($codePromo);
        $em->flush();

        return ["success"=>true,"new_wallet_amount"=>$this->getUser()->getWallet()+$codePromo->getAmount()];
    }

}

