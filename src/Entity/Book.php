<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PreFlush;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: BookRepository::class)]
#[HasLifecycleCallbacks]
#[Table(name: 'books')]
class Book
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    private int $id;

    #[Column(length: 50, unique: true)]
    private string $name;

    #[Column(length: 13, unique: true)]
    private string $isbn;

    #[Column(type: 'json')]
    private array $author;

    //Sqlite cannot save data as datetime in known format.
    #[Column(name: 'publication_year', type: 'bigint')]
    private int $publicationYear;

    #[Column(name: 'created_at', type: 'bigint')]
    private int $createdAt = 0;

    #[Column(name: 'updated_at', type: 'bigint')]
    private int $updatedAt = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }

    public function getAuthor(): array
    {
        return $this->author;
    }

    public function setAuthor(array $author): void
    {
        $this->author = $author;
    }

    public function getPublicationYear(): \DateTimeInterface
    {
        return $this->convertToDatetime($this->publicationYear);
    }

    public function setPublicationYear(\DateTimeInterface $publicationYear): void
    {
        $this->publicationYear = $publicationYear->getTimestamp() * 1000;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->convertToDatetime($this->createdAt);
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->convertToDatetime($this->updatedAt);
    }

    #[PreFlush]
    public function renewUpdatedTimeTime(): void
    {
        $this->updatedAt = time() * 1000;
    }

    #[PrePersist]
    public function generateCreatedAtTime(): void
    {
        if (0 === $this->createdAt) {
            $this->createdAt = time() * 1000;
        }
    }

    private function convertToDatetime(int $timestamp): \DateTimeInterface
    {
        return (new \DateTimeImmutable())->setTimestamp($timestamp/1000);
    }
}
