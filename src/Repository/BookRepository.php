<?php

namespace App\Repository;

use App\Entity\Book;
use App\Enum\LocationEnum;
use App\Entity\InventoryItem;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
    ) {
        $qb = $this->createQueryBuilder('b');
        $qb
            ->andWhere('b.title LIKE :keyword')
            ->orWhere('b.author LIKE :keyword')
            ->orWhere('b.jpTitle LIKE :keyword')
            ->orWhere('b.jpAuthor LIKE :keyword')
            ->setParameter('keyword', "%" . $keyword . "%")
        ;
        return $qb->getQuery()->getResult();
    }

    public function findAllByLocation(LocationEnum $location)
    {
        $qb = $this->createQueryBuilder('b');
        $qb
            ->andWhere('b.location = :location')
            ->setParameter('location', $location->value)
        ;
        return $qb->getQuery()->getResult();
    }
    public function findAllByLocationWithPagination(LocationEnum $location)
    {
        $qb = $this->createQueryBuilder('b');
        $qb
            ->andWhere('b.location = :location')
            ->setParameter('location', $location->value)
        ;
        return $qb->getQuery();
    }

    public function findNoInventory(int $id, LocationEnum $location)
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin(InventoryItem::class, 'ii', 'WITH', 'ii.book = b AND ii.inventory = :inventory')
            ->where('ii.book IS NULL')
            ->andWhere('b.location = :location')
            ->setParameter('inventory', $id)
            ->setParameter('location', $location->value)
        ;
        return $qb->getQuery()->getResult();
    }
}
