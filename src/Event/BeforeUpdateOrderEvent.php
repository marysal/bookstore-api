<?php

namespace App\Event;

use App\Enum\ActionsGroupEnum;
use App\Enum\StatusesOrdersEnum;
use App\Service\ConvertorService;
use phpDocumentor\Reflection\Type;
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

    private $email;

    private $isAdmin;

    private $typeEvent;

    /**
     * @param UserInterface|null $user|null
     * @param Request $request
     */
    public function __construct(?UserInterface $user, Request $request, $type = "create")
    {
        //todo
        if($request->getContentType() == "xml") {
            $request = ConvertorService::xml2Request($request);
        }

        $this->setUser($user);
        $this->setStatus($request);
        $this->setIsAdmin($user);
        $this->setEmail($request);
        $this->setTypeEvent($type);
    }

    /**
     * @param UserInterface|null $user|null
     */
    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    private function setStatus(Request $request): void
    {

        $status = $request->get(
            'status',
            ($this->typeEvent == ActionsGroupEnum::UPDATE) ? null : StatusesOrdersEnum::STATUS_PENDING
        );

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

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param Request $request
     */
    private function setEmail(Request $request): void
    {
        $email = $request->get('email', null);
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getTypeEvent(): string
    {
        return $this->typeEvent;
    }

    /**
     * @param $type
     */
    private function setTypeEvent($type)
    {
        $this->typeEvent = $type;
    }
}