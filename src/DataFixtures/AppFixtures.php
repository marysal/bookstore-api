<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\StatusesOrdersEnum;
use App\Enum\TypesBooksENum;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $faker;
    private $books = [];
    private $authors = [];
    private $orders = [];
    private $phones = [
        "375292571200",
        "375292571211",
        "375332571211"
    ];
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->faker = Factory::create();
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager): void
    {
        $this->loadAuthors($manager);
        $this->loadBooks($manager);
        $this->loadAuthorsToBooks($manager);
        $this->loadAdmin($manager);
        $this->loadOrder($manager);
        $this->loadOrderBook($manager);
 ;

        $manager->flush();
    }

    private function loadAuthors(ObjectManager $manager):void
    {
        for ($i = 1; $i <= 5; $i++) {
            $author = new Author();
            $author->setName($this->faker->name);
            $manager->persist($author);
            $this->authors[$i] = $author;
        }
    }

    public function loadBooks(ObjectManager $manager): void
    {
        $randomTypeBooks = TypesBooksENum::getTypesBooksList();

       for ($i = 1; $i <= 20; $i++) {
           $randomKeyTypeBooks = array_rand($randomTypeBooks);
           $book = new Book();
           $book->setTitle($this->faker->title);
           $book->setDescription($this->faker->text(500));
           $book->setType($randomTypeBooks[$randomKeyTypeBooks]);
           $manager->persist($book);
           $this->books[$i] = $book;
       }
    }

    private function loadAuthorsToBooks(ObjectManager $manager): void
    {
        foreach (range(0, count($this->books)) as $iteration) {
            $randomBook = rand(1, 5);
            $randomAuthor = rand(1, 5);
            $this->books[$randomBook]->appendAuthor($this->authors[$randomAuthor]);
        }
    }

    private function loadAdmin(ObjectManager $manager): void
    {
        $admin = new User();
        $admin
            ->setEmail("admin@admin.admin")
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->encoder->encodePassword($admin, "123456"));
        $manager->persist($admin);
    }

    private function loadOrder(ObjectManager $manager):void
    {
        $randomPhoneIndex = array_rand($this->phones);
        $statuses = StatusesOrdersEnum::getTypesBooksList();

        for ($i = 1; $i < 5; $i++) {
            $randomKeyStatuses = array_rand($statuses);
            $order = new Order();
            $order->setAddress($this->faker->address);
            $order->setStatus($statuses[$randomKeyStatuses]);
            $order->setPhone($this->phones[$randomPhoneIndex]);
            $this->orders[$i] = $order;
            $manager->persist($order);

        }
    }

    private function loadOrderBook(ObjectManager $manager): void
    {
        $orders = count($this->orders);
        for ($i = 1; $i < $orders; $i++) {
            $randomBook = array_rand($this->books);
            $this->orders[$i]->appendBookOrderList($this->books[$randomBook]);
        }
    }


}
