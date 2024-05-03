<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

//    /**
//     * @return User[] Returns an array of User objects

//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }


//    */
    public function FindByusername($username): array
{
        return $this->createQueryBuilder('count(u.id)')
           ->andWhere('u.username = :val')
           ->setParameter('val', $username)
           ->getQuery()
           ->getSingleResult()
      ;
   }
   public function FindByemail($email): array
   {
           return $this->createQueryBuilder('count(u.id)')
              ->andWhere('u.email = :val')
              ->setParameter('val', $email)
              ->getQuery()
              ->getSingleResult()
         ;
      }
      public function FindBynumber($tel): array
{
        return $this->createQueryBuilder('count(u.id)')
           ->andWhere('u.num_telephone = :val')
           ->setParameter('val', $tel)
           ->getQuery()
           ->getSingleResult()
      ;
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

}

