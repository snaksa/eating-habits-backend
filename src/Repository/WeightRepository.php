<?php

namespace App\Repository;

use App\Entity\Weight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Weight|null find($id, $lockMode = null, $lockVersion = null)
 * @method Weight|null findOneBy(array $criteria, array $orderBy = null)
 * @method Weight[]    findAll()
 * @method Weight[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Weight::class);
    }

    /**
     * @param int $id
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Weight
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Weight $weight
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Weight $weight)
    {
        $this->_em->persist($weight);
        $this->_em->flush();
    }

    /**
     * @param Weight $weight
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Weight $weight)
    {
        $this->_em->remove($weight);
        $this->_em->flush();
    }
}
