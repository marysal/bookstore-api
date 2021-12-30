<?php

namespace App\EventSubscriber;

use App\Event\BeforeUpdateOrderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BeforeUpdateOrderSubscriber implements EventSubscriberInterface
{
    public function onOrderPreUpdate(BeforeUpdateOrderEvent $event)
    {
        if(!$event->getIsAdmin() && !empty($event->getStatus())) {
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                "Invalid credentials."
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'order.pre_update' => 'onOrderPreUpdate'
        ];
    }
}
