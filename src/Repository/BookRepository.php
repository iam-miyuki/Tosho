<?php

namespace App\Repository;

use App\Entity\Book;
use App\Enum\LocationEnum;
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

    public function findOneByCode(string $code)
    {
        $qb = $this->createQueryBuilder('b');
        $qb
            ->andWhere('b.code = :code')
            ->setParameter('code', $code);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllWithFilterQuery(
        string $keyword
    )
    {
        $qb = $this->createQueryBuilder('b');
        $qb
            ->andWhere('b.title LIKE :keyword')
            ->orWhere('b.author LIKE :keyword')
            ->orWhere('b.jpTitle LIKE :keyword')
            ->orWhere('b.jpAuthor LIKE :keyword')
            ->setParameter('keyword',"%" . $keyword . "%")
            ;
        return $qb->getQuery()->getResult();
    }

    public function findAllByLocation(LocationEnum $location){
        $qb = $this->createQueryBuilder('b');
        $qb
        ->andWhere('b.location = :location')
        ->setParameter('location',$location->value)
        ;
        return $qb->getQuery()->getResult();
    }
    public function findAllByLocationWithPagination(LocationEnum $location){
        $qb = $this->createQueryBuilder('b');
        $qb
        ->andWhere('b.location = :location')
        ->setParameter('location',$location->value)
        ;
        return $qb->getQuery();
    }
}
