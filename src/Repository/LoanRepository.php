<?php

namespace App\Repository;

use App\Entity\Loan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Family>
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }
    
    public function findByFamily(string $name)
    {
        $qb = $this->createQueryBuilder('loan');
        $qb
            ->addSelect('family')
            ->addSelect('book')
            ->leftJoin('loan.family', 'family')
            ->leftJoin('loan.book', 'book')
            ->where('family.name = :name')
            ->setParameter('name', $name)
            ;

        return $qb->getQuery()->getResult();
    }
    public function findByBookCode(string $bookCode)
    {
        $qb = $this->createQueryBuilder('loan');
        $qb
            ->addSelect('book')
            ->leftJoin('loan.book', 'book')
            ->where('book.bookCode = :code')
            ->setParameter('code', $bookCode)
            ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
