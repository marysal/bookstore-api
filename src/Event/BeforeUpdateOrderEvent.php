<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class BeforeUpdateOrderEvent extends Event
{
    /**
     * @var $user UserInterface
     */
    private $user;

    private $status;

    private $isAdmin;

    /**
     * @param UserInterface|null $user|null
     * @param Request $request
     */
    public function __construct(?UserInterface $user, Request $request)
    {
        $this->setUser($user);
        $this->setStatus($request);
        $this->setIsAdmin($user);
    }

    /**
     * @param UserInterface|null $user|null
     */
    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    private function setStatus(Request $request): void
    {
        $status = $request->get('status', false);
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getIsAdmin(): string
    {
        return $this->isAdmin;
    }

    /**
     * @param UserInterface|null $user|null
     */
    private function setIsAdmin(?UserInterface $user): void
    {
        $isAdmin = false;

        if ($this->user && in_array("ROLE_ADMIN", $this->user->getRoles())) {
            $isAdmin = true;
        }

        $this->isAdmin = $isAdmin;
    }
}