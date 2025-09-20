<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    // //    /**
    // //     * @return User[] Returns an array of User objects
    // //     */
    public function findAllWithFilterQuery(string $role, string $query): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.roles LIKE :role')
            ->andWhere(
                $qb->expr()->orX( //orX : creer un OR entre les conditions, expr : nécessaire pour combiner AND et OR
                    $qb->expr()->like('u.firstName', ':query'), 
                    $qb->expr()->like('u.lastName', ':query'),
                    $qb->expr()->like('u.jpFirstName', ':query'),
                    $qb->expr()->like('u.jpLastName', ':query'),
                    $qb->expr()->like('u.email', ':query')
                )
            )
            ->setParameter('role', '%' . $role . '%')
            ->setParameter('query', '%' . $query . '%');

        return $qb->getQuery()->getResult();
    }


    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
