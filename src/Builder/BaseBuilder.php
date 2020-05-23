<?php

namespace App\Builder;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class BaseBuilder
{
    public function findEntity(int $id, ServiceEntityRepository $repository)
    {
        return $repository->findOneById($id);
    }
}
