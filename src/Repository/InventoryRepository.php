<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Enum\InventoryStatusEnum;
use App\Enum\LocationEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findAllByStatus(InventoryStatusEnum $status): array
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->andWhere('i.status= :status')
            ->setParameter('status', $status->value)
        ;
        return $qb->getQuery()->getResult();
    }

    public function findAllWithFilterQuery(
        ?InventoryStatusEnum $status,
        ?string $date,
        ?LocationEnum $location // ? = autoriser les valeurs null
    ) {
        $qb = $this->createQueryBuilder('i');
        if ($status != null) {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }
        if ($date != null) {
            $qb->andWhere('i.date = :date')
                ->setParameter('date', $date);
        }
        if ($location != null) {

            $qb->andWhere('i.location = :location')
                ->setParameter('location', $location);
        }
        return $qb->getQuery()->getResult();
    }

    public function findWithItems($id)
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->addSelect('inventoryItems')
            ->leftJoin('i.inventoryItems','inventoryItems')
            ->where('i.id = :id')
            ->setParameter('id',$id)
            ;
        return $qb->getQuery()->getOneOrNullResult();
    }




    //    /**
    //     * @return Inventory[] Returns an array of Inventory objects
    //     */
    //    public function findByExampleField($value): array
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

    //    public function findOneBySomeField($value): ?Inventory
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
