<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Inventory;
use App\Entity\InventoryItem;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<InventoryItem>
 */
class InventoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryItem::class);
    }

     public function findOneByInventoryAndBook(Inventory $inventory,Book $book,) : ?InventoryItem
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->andWhere('i.inventory = :inventory')
            ->andWhere('i.book = :book')
            ->setParameter('inventory', $inventory)
            ->setParameter('book', $book)
        ;
        return $qb->getQuery()->getOneOrNullResult();
    }

   
//  public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InventoryItem
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
