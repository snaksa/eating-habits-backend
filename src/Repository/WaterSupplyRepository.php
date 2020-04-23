<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WaterSupply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WaterSupply|null find($id, $lockMode = null, $lockVersion = null)
 * @method WaterSupply|null findOneBy(array $criteria, array $orderBy = null)
 * @method WaterSupply[]    findAll()
 * @method WaterSupply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WaterSupplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WaterSupply::class);
    }

    /**
     * @param int $id
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?WaterSupply
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserWaterSupplies(User $user)
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :id')
            ->setParameters(['id' => $user->getId()])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param WaterSupply $waterSupply
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(WaterSupply $waterSupply)
    {
        $this->_em->persist($waterSupply);
        $this->_em->flush();
    }

    /**
     * @param WaterSupply $waterSupply
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(WaterSupply $waterSupply)
    {
        $this->_em->remove($waterSupply);
        $this->_em->flush();
    }
}
