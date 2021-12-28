<?php

namespace App\Entity;

use App\Enum\StatusesOrdersEnum;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`orders`")
 * @ORM\HasLifecycleCallbacks()
 */
class Order
{
    public function __construct()
    {
        $this->bookOrderList = new ArrayCollection();
    }

    /**
     * @Groups("order")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups("order")
     * @ORM\Column(type="string", length=20, nullable=false)
     * @CustomAssert\PhoneConstraint()
     * @CustomAssert\ApiPhoneConstraint(groups="notests")
     */
    private $phone;

    /**
     * @Groups("order")
     * @Groups("book")
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=255)
     */
    private $address;

    /**
     * @Groups("order")
     * @ORM\Column(type="string", length=15)
     * @Assert\Choice({"pending", "processed", "delivered"})
     */
    private $status;

    /**
     * @Groups("order")
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @Groups("order")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @Groups("book_order")
     * @ORM\ManyToMany(targetEntity="App\Entity\Book")
     */
    private $bookOrderList;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection|Book[]
     */
    public function getBookOrderList(): Collection
    {
        return $this->bookOrderList;
    }

    /**
     * @param Book $book
     */
    public function appendBookOrderList(Book $book): void
    {
        if (!$this->bookOrderList->contains($book)) {
            $this->bookOrderList[] = $book;
        }
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function deleteBookOrderList(Book $book): self
    {
        if ($this->bookOrderList->contains($book)) {
            $this->bookOrderList->removeElement($book);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setStatusValue(): void
    {
        $this->status = StatusesOrdersEnum::STATUS_PENDING;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }


}
