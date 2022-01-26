<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Tests\ORM\Functional\Ticket\DDC1509AbstractFile;
use Doctrine\Tests\ORM\Functional\Ticket\DDC1509File;
use Doctrine\Tests\ORM\Functional\Ticket\DDC1509Picture;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class RemoveOldOrdersCommand extends Command
{
    const AGE_ORDERS = 30;

    protected static $defaultName = 'RemoveOldOrdersCommand';
    protected static $defaultDescription = 'Delete old orders';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @param string|null $name
     * @param ContainerInterface $container
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        ContainerInterface $container,
        OrderRepository $orderRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->container = $container;
        $this->orderRepository = $orderRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('age_orders', null, InputOption::VALUE_OPTIONAL, 'Age of orders to be deleted')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $countOrders = $countUndeletedOrders = $deletetedOrders = 0;
        $age_orders = $input->getOption('age_orders') ?? self::AGE_ORDERS;

        try {
            /**
             * @var $em EntityManager
             */
            $em = $this->container->get('doctrine')->getManager();

            $orders = $this->orderRepository->getOrdersForPreviewDaysQuery($age_orders);
            $countOrders = $orders->getResult()->count();

            foreach ($orders->iterate() as $order) {
                $em->remove($order[0]);
                $em->flush();
                $deletetedOrders++;
            }
        } catch (Throwable $exception) {
        } finally {
            $countUndeletedOrders = $countOrders - $deletetedOrders;
            $io = new SymfonyStyle($input, $output);

            $io->success(
                'Found orders to delete: ' . $countOrders .
                '.  Deleted: ' . $deletetedOrders .
                '. Failed to delete' . $countUndeletedOrders
            );

            return 0;
        }
    }
}
