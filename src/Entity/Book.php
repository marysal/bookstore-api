<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 * @ORM\Table(name="`books`")
 */
class Book
{

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(max=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Choice({"prose", "poetry"})
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", mappedBy="authors")
     */
    private $books;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ArrayCollection|Book[]
     */
    public function getBooks(): ArrayCollection
    {
        return $this->books;
    }

    /**
     * @param Book $book
     * @return Book
     */
    public function setBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            //$book->addAuthor($this);
        }

        return $this;
    }


    /**
     * @param Book $book
     * @return Book
     */
    public function deleteBook(Book $book): self
    {
        if ($this->books->contains($book)) {
            $this->books->removeElement($book);
        }

        return $this;
    }
}
