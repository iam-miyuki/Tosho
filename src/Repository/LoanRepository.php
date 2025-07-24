<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Family;
use App\Enum\LoanStatusEnum;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Family>
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function findAllByStatus(LoanStatusEnum $status)
    {
        $qb = $this->createQueryBuilder('l');
        $qb
            ->where('l.status = :status')
            ->setParameter('status',$status);
        return $qb->getQuery()->getResult();
    }
    
    public function findAllWithFamilyAndStatus(Family $family)
    {
        $qb = $this->createQueryBuilder('l');
        $qb
            ->addSelect('book')
            ->leftJoin('l.book','book')
            ->andWhere('l.family = :family')
            ->andWhere('l.status = :status')
            ->setParameter('family',$family)
            ->setParameter('status',LoanStatusEnum::inProgress)
            ;
        return $qb->getQuery()->getResult();
    }

    // trouver un loan par livre et statut = en cours
    public function findWithBookAndStatus(Book $book)
    {
        $qb = $this->createQueryBuilder('l');
        $qb
        ->addSelect('family')
        ->leftJoin('l.family', 'family')
        ->andWhere('l.book = :book')
        ->andWhere("l.status = :status") 
        ->setParameter('book', $book)
        ->setParameter('status', LoanStatusEnum::inProgress);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
