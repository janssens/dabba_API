<?php
// src/EventListener/ZoneChangedNotifier.php
namespace App\EventListener;

use App\Entity\Zone;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ZoneChangedNotifier
{
    public function onFlush(Zone $zone, LifecycleEventArgs $eventArgs)
    {

    }
}
