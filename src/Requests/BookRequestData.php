<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class BookRequestData
{
    #[Length(max: 50)]
    #[NotBlank]
    private string $name;

    #[Length(min: 13, max: 13)]
    #[NotBlank]
    private string $isbn;

    #[NotBlank]
    private array $author;

    #[NotBlank]
    private \DateTimeInterface $publicationYear;

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
        return $this->publicationYear;
    }

    public function setPublicationYear(\DateTimeInterface $publicationYear): void
    {
        $this->publicationYear = $publicationYear;
    }
}
