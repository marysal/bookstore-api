<?php

namespace App\EventSubscriber;

use App\Enum\ActionsGroupEnum;
use App\Event\BeforeUpdateOrderEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;

class BeforeUpdateOrderSubscriber implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param MailerInterface $mailer
     * @param ContainerInterface $container
     */
    public function __construct(MailerInterface $mailer, ContainerInterface $container)
    {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function onOrderPreUpdate(BeforeUpdateOrderEvent $event)
    {
        if (!$event->getIsAdmin() && !empty($event->getStatus())) {
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                "Only admin can change order status."
            );
        }
    }

    public function onOrderAfterValidate(BeforeUpdateOrderEvent $event)
    {
        $sender = $this->container->getParameter('app.sender');

        if (!empty($event->getEmail()) && $this->isNewStatus($event)) {

            $email = (new TemplatedEmail())
                ->from($sender)
                ->to($event->getEmail())
                ->subject('Time for Symfony Mailer!')
                ->htmlTemplate('emails/status_mail.html.twig')
                ->context([
                    'status' => $event->getStatus()
                ]);

            $this->mailer->send($email);
        }
    }

    private function isNewStatus(BeforeUpdateOrderEvent $event): bool
    {
        return $event->getTypeEvent() != ActionsGroupEnum::UPDATE || !empty($event->getStatus());
    }

    public static function getSubscribedEvents()
    {
        return [
            'order.pre_update' => 'onOrderPreUpdate',
            'order.after_validate' => 'onOrderAfterValidate'
        ];
    }
}
