<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RemoveOldOrdersCommand extends Command
{
    protected static $defaultName = 'RemoveOldOrdersCommand';
    protected static $defaultDescription = 'Add a short description for your command';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(string $name = null, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        /**
         * @var $em EntityManager
         */
        $em = $this->container->get('doctrine')->getManager();
        $orders = $em->createQuery("SELECT o from App:Order o where o.createdAt <= :date")
                    ->setParameter('date', new \DateTime('-30 days'));

        foreach ($orders->iterate() as $order) {
            $em->remove($order[0]);
            $em->flush();
        }

        $io = new SymfonyStyle($input, $output);
        $io->success('Orders older than one month have been deleted.');

        return 0;
    }
}
