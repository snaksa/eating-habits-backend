<?php

namespace App\Repository;

use App\Entity\MedicineIntake;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MedicineIntake|null find($id, $lockMode = null, $lockVersion = null)
 * @method MedicineIntake|null findOneBy(array $criteria, array $orderBy = null)
 * @method MedicineIntake[]    findAll()
 * @method MedicineIntake[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MedicineIntakeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicineIntake::class);
    }

    // /**
    //  * @return MedicineIntake[] Returns an array of MedicineIntake objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MedicineIntake
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
