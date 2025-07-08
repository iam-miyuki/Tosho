<?php

namespace App\Repository;

use App\Entity\Family;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Family>
 */
class FamilyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Family::class);
    }
    public function findAllByName(string $familyName)
    {
        $qb = $this->createQueryBuilder('family');
        $qb
            ->addSelect('members')
            ->leftJoin('family.members', 'members')
            ->where('family.name = :familyName')
            ->setParameter('familyName', $familyName);
        return $qb->getQuery()->getResult();
    }

    public function findOneById(int $familyId)
    {
        $qb = $this->createQueryBuilder('family');
        $qb
            ->where('family.id = :familyId')
            ->setParameter('familyId', $familyId);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
