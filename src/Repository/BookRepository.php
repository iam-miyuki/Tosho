<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Family>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByBookCode(string $bookCode)
    {
        $qb = $this->createQueryBuilder('book');
        $qb
            ->andWhere('book.code = :bookCode')
            ->setParameter('bookCode', $bookCode);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
