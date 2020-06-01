<?php

namespace App\Repository;

use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /**
     * @param int $id
     * @return MedicineIntake|null
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?MedicineIntake
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findExistingIntake(
        MedicineSchedule $medicineSchedule,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ) {
        return $this->createQueryBuilder('t')
            ->where('t.medicineSchedule = :id AND t.date >= :startDate AND t.date < :endDate')
            ->setParameters([
                'id' => $medicineSchedule->getId(),
                'startDate' => $startDate,
                'endDate' => $endDate
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param MedicineIntake $medicineIntake
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MedicineIntake $medicineIntake)
    {
        $this->_em->persist($medicineIntake);
        $this->_em->flush();
    }

    /**
     * @param MedicineIntake $medicineIntake
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(MedicineIntake $medicineIntake)
    {
        $this->_em->remove($medicineIntake);
        $this->_em->flush();
    }
}
