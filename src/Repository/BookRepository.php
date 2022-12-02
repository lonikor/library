<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function getAllSortedByName(): Collection
    {
        $result = $this->createQueryBuilder('b')
            ->addOrderBy('b.name', 'ASC')
            ->getQuery()
            ->execute();

        return new ArrayCollection($result);
    }
}
