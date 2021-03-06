<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AuthorRepository::class)
 * @ORM\Table(name="`authors`")
 */
class Author
{
    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    /**
     * @Groups("author")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups("author")
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=10, max=255)
     * @Assert\NotBlank()
     *
     */
    private $name;


    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @Groups("book_detail")
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", mappedBy="authors")
     */
    private $books;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection|Book[]
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function appendBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
        }

        return $this;
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function deleteBook(Book $book): self
    {
        if ($this->books->contains($book)) {
            $this->books->removeElement($book);
        }

        return $this;
    }
}
