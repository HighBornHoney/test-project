<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['book_basic'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 3)]
    #[Groups(['book_basic'])]
    private ?string $title = null;

    #[ORM\Column(length: 4, nullable: true)]
    #[Assert\Length(exactly: 4)]
    #[Groups(['book_basic'])]
    private ?string $year = null;

    /**
     * @var Collection<int, Author>
     */
    #[ORM\ManyToMany(targetEntity: Author::class, inversedBy: 'books')]
    #[Groups(['book_basic'])]
    private Collection $authors;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['book_basic'])]
    private ?Publisher $publisher = null;

    #[Assert\Type('list')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    #[Assert\Unique]
    #[Assert\Count(max: 5)]
    private ?array $author_ids = null;

    #[Assert\Type('integer')]
    private ?int $publisher_id = null;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): static
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): static
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }

        return $this;
    }

    public function removeAuthor(Author $author): static
    {
        $this->authors->removeElement($author);

        return $this;
    }

    public function getPublisher(): ?Publisher
    {
        return $this->publisher;
    }

    public function setPublisher(?Publisher $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getAuthorIds(): ?array
    {
        return $this->author_ids;
    }

    public function setAuthorIds(?array $author_ids): static
    {
        $this->author_ids = $author_ids;

        return $this;
    }

    public function getPublisherId(): ?int
    {
        return $this->publisher_id;
    }

    public function setPublisherId(?int $publisher_id): static
    {
        $this->publisher_id = $publisher_id;

        return $this;
    }
}
