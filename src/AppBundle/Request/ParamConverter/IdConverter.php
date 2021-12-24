<?php
namespace AppBundle\Request\ParamConverter;

use App\Entity\Author;
use App\Entity\Order;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Book;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdConverter implements ParamConverterInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $entityName = $configuration->getClass();

        $object = $this->em->getRepository($entityName)->findOneBy([
            'id'   => $request->attributes->get('id'),
        ]);

        if (null === $object) {
            throw new NotFoundHttpException("Object not found");
        }

        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return in_array($configuration->getClass(), [Book::class, Author::class, Order::class]);
    }
}