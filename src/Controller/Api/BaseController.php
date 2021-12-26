<?php

namespace App\Controller\Api;

use App\Enum\EntityGroupsEnum;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\OrderRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @var PaginatorService
     */
    protected $paginator;

    /**
     * @param BookRepository $bookRepository
     * @param AuthorRepository $authorRepository
     * @param OrderRepository $orderRepository
     * @param EntityManagerInterface $manager
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param PaginatorService $paginator
     */
    public function __construct(
        BookRepository         $bookRepository,
        AuthorRepository       $authorRepository,
        OrderRepository        $orderRepository,
        EntityManagerInterface $manager,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator,
        PaginatorService       $paginator
    )
    {

        $this->entityManager = $manager;
        $this->serializer = $serializer;
        $this->bookRepository = $bookRepository;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
        $this->orderRepository = $orderRepository;
        $this->paginator = $paginator;
    }

    /**
     * @param $data
     * @param string $typeJsonContent
     * @return string
     */
    protected function getJsonContent($data, string $typeJsonContent = "books"): string
    {
        $group = EntityGroupsEnum::getEntityGroupsList();

        return $this->serializer->serialize(
            [
                'data' => $data
            ],
            'json',
            [
                'groups' => $group[$typeJsonContent]
            ]
        );
    }

    /**
     * @param $entity
     * @throws NotFoundHttpException
     * @return bool|null
     */
    protected function validate($entity): void
    {
        if (empty($entity)) {
            throw $this->createNotFoundException('Object with this ID not found');
        }


        $errors = $this->validator->validate($entity);

        if ($errors->has(0)) {
            throw $this->createNotFoundException(
                $errors->get(0)->getPropertyPath(). ": ".$errors->get(0)->getMessage()
            );
        }
    }
}