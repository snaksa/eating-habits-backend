<?php

namespace App\Repository;

use App\Entity\Medicine;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Medicine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Medicine|null findOneBy(array $criteria, array $orderBy = null)
 * @method Medicine[]    findAll()
 * @method Medicine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MedicineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medicine::class);
    }

    public function findUserMedicines(User $user)
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :id')
            ->setParameters(['id' => $user])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return Medicine|null
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Medicine
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Medicine $medicine
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Medicine $medicine)
    {
        $this->_em->persist($medicine);
        $this->_em->flush();
    }

    /**
     * @param Medicine $medicine
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Medicine $medicine)
    {
        $this->_em->remove($medicine);
        $this->_em->flush();
    }
}
