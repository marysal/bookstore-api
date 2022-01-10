<?php

namespace App\EventSubscriber;

use App\Event\BeforeUpdateOrderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class BeforeUpdateOrderSubscriber implements EventSubscriberInterface
{
    protected $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onOrderPreUpdate(BeforeUpdateOrderEvent $event)
    {
        if(!$event->getIsAdmin() && !empty($event->getStatus())) {
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                "Only admin can change order status."
            );
        }


        //var_dump($event->getStatus());die();

        if(!empty($event->getEmail())) {
            $email = (new Email())
                ->from('hello@example.com')
                ->to($event->getEmail())
                ->subject('Time for Symfony Mailer!')
                ->text('Sending emails is fun again!')
                ->html("<p>Status have been changed to ".$event->getStatus()."</p>");

            $this->mailer->send($email);
        }



    }

    public static function getSubscribedEvents()
    {
        return [
            'order.pre_update' => 'onOrderPreUpdate'
        ];
    }
}
