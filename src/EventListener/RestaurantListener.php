<?php
// src/EventListener/StockListener.php
namespace App\EventListener;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Entity\Zone;
use App\Exception\DabbaException;
use App\Service\Place;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage as TokenStorage;
use Symfony\Component\Security\Core\Security;

class RestaurantListener
{
    /**
     * @var Place
     */
    private $place;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    public function __construct(Place $place,TokenStorage $token_storage)
    {
        $this->place = $place;
        $this->token_storage = $token_storage;
    }

    public function prePersist(Restaurant $restaurant, \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getObjectManager();
        $exist = $em->getRepository(Restaurant::class)->findOneBy(['name'=>$restaurant->getName()]);
        if ($exist){
            throw new \Exception('This name is already used');
        }
        $data = $this->place->search($restaurant->getName(),$restaurant->getFormattedAddress());
        if (!isset($data['error'])){
            if (count($data['success'])>1){
                throw new \Exception('Not only one result');
            }else{
                $found = $data['success'][0];
                $restaurant->setLat($found['geometry']['location']['lat']);
                $restaurant->setLng($found['geometry']['location']['lng']);
                $restaurant->setFormattedAddress($found['formatted_address']);
                $restaurant->setGooglePlaceId($found['place_id']);

                $exist = $em->getRepository(Restaurant::class)->findOneBy(['google_place_id'=>$restaurant->getGooglePlaceId()]);
                /** @var Restaurant $exist */
                if ($exist){
                    throw new \Exception('A restaurant with the same google place id already exist : #'.$exist->getId().' '.$exist->getName().' (google place id = "'.$exist->getGooglePlaceId().'")');
                }
                $details = $this->place->getDetails($found['place_id']);
                if (isset($details['success'])){
                    if (isset($details['success']['opening_hours'])) {
                        $restaurant->setOpeningHours($details['success']['opening_hours']['weekday_text']);
                    }
                    if (isset($details['success']['website'])){
                        $restaurant->setWebsite($details['success']['website']);
                    }
                    if (isset($details['success']['formatted_phone_number'])) {
                        $restaurant->setPhone($details['success']['formatted_phone_number']);
                    }
                }
                /** @var User $user */
                $user = $this->token_storage->getToken()->getUser();
                $zone = $user->getZone();
                if (!$zone){
                    $zone = $em->getRepository(Zone::class)->findDefault();
                }
                $restaurant->setZone($zone);
            }
        }
        $zone = $restaurant->getZone();
        if (!$zone){
            $em = $eventArgs->getObjectManager();
            $zone = $em->getRepository(Zone::class)->findDefault();
            $restaurant->setZone($zone);
        }
    }
}
