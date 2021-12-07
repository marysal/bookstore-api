<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @CustomAssert\PhoneConstraint()
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(min=5, max=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Choice({"pending", "processed", "delivered"})
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }
}
