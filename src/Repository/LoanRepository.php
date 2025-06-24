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
    public function loanList()
    {
        $this->entityManager->getRepository(Loan::class)->findAll();    
    }
}
