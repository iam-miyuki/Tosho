<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Enum\InventoryItemStatusEnum;
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

    public function findOneByInventoryAndBook(Inventory $inventory, Book $book,): ?InventoryItem
    {
        $qb = $this->createQueryBuilder('ii');
        $qb
            ->andWhere('ii.inventory = :inventory')
            ->andWhere('ii.book = :book')
            ->setParameter('inventory', $inventory)
            ->setParameter('book', $book)
        ;
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllByInventory(Inventory $inventory)
    {
        $qb = $this->createQueryBuilder('ii');
        $qb
            ->addSelect('book')
            ->leftJoin('ii.book','book')
            ->andWhere('ii.inventory = :inventory')
            ->setParameter('inventory', $inventory)
        ;
        return $qb->getQuery()->getResult();
    }

    public function findAllByInventoryAndStatus(Inventory $inventory, InventoryItemStatusEnum $status)
    {
        $qb = $this->createQueryBuilder('ii');
        $qb
            ->andWhere('ii.inventory = :inventory')
            ->andWhere('ii.status = :status')
            ->setParameter('inventory', $inventory)
            ->setParameter('status',$status->value)
        ;
        return $qb->getQuery()->getResult();
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
