<?php
// src/EventListener/ZoneChangedNotifier.php
namespace App\EventListener;

use App\Entity\Order;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ZoneListener
{
    public function preUpdate(Zone $zone, LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('is_default') && $eventArgs->getNewValue('is_default')) {
            $this->removeOldDefault($eventArgs->getEntityManager(),$zone);
        }else{
            $default = $eventArgs->getEntityManager()->getRepository(Zone::class)->findDefaultButThisOne($zone);
            if (!$default){
                $zone->setIsDefault(true);
            }
        }
    }

    public function prePersist(Zone $zone, LifecycleEventArgs $eventArgs)
    {
        if ($zone->getIsDefault()) {
            $this->removeOldDefault($eventArgs->getEntityManager(),$zone);
        }else{
            $default = $eventArgs->getEntityManager()->getRepository(Zone::class)->findDefault();
            if (!$default){
                $zone->setIsDefault(true);
            }
        }
    }

    public function postRemove(Zone $zone,LifecycleEventArgs $eventArgs){
        /** @var EntityManager $em */
        $em = $eventArgs->getEntityManager();
        /** @var ZoneRepository $zone_repo */
        $zone_repo = $em->getRepository(Zone::class);
        $default = $zone_repo->findDefault();
        if (!$default){
            $new_default = $zone_repo->findAll();
            if (count($new_default)){
                $new_default[0]->setIsDefault(true);
                $em->persist($new_default[0]);
                $em->flush();
            }
        }
    }

    private function removeOldDefault(EntityManager $em,Zone $zone){
        /** @var Zone $old_default */
        if ($zone->getId())
            $old_default = $em->getRepository(Zone::class)->findDefaultButThisOne($zone);
        else
            $old_default = $em->getRepository(Zone::class)->findDefault($zone);
        if ($old_default){
            $em->getUnitOfWork()->scheduleExtraUpdate($old_default, array(
                'is_default' => array(true, false)
            ));
        }
    }
}
