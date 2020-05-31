<?php

namespace App\Repository;

use App\Constant\MedicineFrequencies;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MedicineSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method MedicineSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method MedicineSchedule[]    findAll()
 * @method MedicineSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MedicineScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicineSchedule::class);
    }

    public function findUserEverydayAndOnceScheduledMedicinesByDay(
        User $user,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ) {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.medicine', 'm')
            ->where('m.user = :id AND ((m.frequency = :everyday AND t.intake_time < :endDate) '
                . 'OR (t.intake_time >= :startDate AND t.intake_time < :endDate AND m.frequency = :once))')
            ->setParameters([
                'id' => $user->getId(),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'everyday' => MedicineFrequencies::EVERYDAY,
                'once' => MedicineFrequencies::ONCE
            ])
            ->getQuery()
            ->getResult();
    }

    public function findUserPeriodMedicines(User $user)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.medicine', 'm')
            ->where('m.user = :id AND m.frequency = :period')
            ->setParameters([
                'id' => $user->getId(),
                'period' => MedicineFrequencies::PERIOD
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return MedicineSchedule|null
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?MedicineSchedule
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param MedicineSchedule $medicineSchedule
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(MedicineSchedule $medicineSchedule)
    {
        $this->_em->persist($medicineSchedule);
        $this->_em->flush();
    }

    /**
     * @param MedicineSchedule $medicineSchedule
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(MedicineSchedule $medicineSchedule)
    {
        $this->_em->remove($medicineSchedule);
        $this->_em->flush();
    }
}
