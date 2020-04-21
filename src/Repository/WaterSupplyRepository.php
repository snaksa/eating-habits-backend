<?php

namespace App\Repository;

use App\Entity\WaterSupply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
