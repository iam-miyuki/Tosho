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
    
    public function findByFamilyName(string $familyName)
    {
        $qb = $this->createQueryBuilder('loan');
        $qb
        ->addSelect('family')
        ->addSelect('book')
        ->leftJoin('loan.family', 'family')
        ->leftJoin('loan.book', 'book')
        ->where('family.name = :name')
        ->andWhere("loan.loanStatus != :status") // afficher la liste des prêts non rendu
        ->setParameter('name', $familyName)
        ->setParameter('status', 'Rendu');
        // dd($qb->getQuery()->getSQL());
        return $qb->getQuery()->getResult();

        // TODO : s'occuper de cas ou il y a deux comptes au même nom, meme s'il n'y a pas de prets en cours
    }

    public function findByFamilyId(string $familyId)
    {
        $qb = $this->createQueryBuilder('loan');
        $qb
        ->addSelect('family')
        ->addSelect('book')
        ->leftJoin('loan.family', 'family')
        ->leftJoin('loan.book', 'book')
        ->where('family.id = :id')
        ->andWhere("loan.loanStatus != :status") // afficher la liste des prêts non rendu
        ->setParameter('id', $familyId)
        ->setParameter('status', 'Rendu');
        // dd($qb->getQuery()->getSQL());
        return $qb->getQuery()->getResult();
    }

    

   

   
}
