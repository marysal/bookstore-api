<?php

namespace App\Controller\Api;

use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Serializer|SerializerInterface
     */
    protected $serializer;

    /**
     * @var BookRepository
     */
    protected $bookRepository;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var AuthorRepository
     */
    protected $authorRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @param BookRepository $bookRepository
     * @param AuthorRepository $authorRepository
     * @param OrderRepository $orderRepository
     * @param EntityManagerInterface $manager
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        BookRepository $bookRepository,
        AuthorRepository $authorRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $manager;
        $this->serializer = $serializer;
        $this->bookRepository = $bookRepository;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
        $this->orderRepository = $orderRepository;
    }
}