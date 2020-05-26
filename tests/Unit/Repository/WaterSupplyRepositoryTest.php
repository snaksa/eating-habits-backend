<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WaterSupplyFixtures;
use App\Entity\User;
use App\Entity\WaterSupply;
use App\Repository\WaterSupplyRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class WaterSupplyRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private WaterSupplyRepository $repository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(WaterSupply::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WaterSupplyFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_water_supplies(): void
    {
        $fixtureWeights = $this->filterFixtures(function ($entity) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $this->user->getId();
        });

        $waterSupplies = $this->repository->findUserWaterSupplies($this->user);

        $this->assertEquals(count($fixtureWeights), count($waterSupplies));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $waterSupplyFixture = $this->fixtures->getReference('user_demo_water_supply_0_0');
        $waterSupply = $this->repository->findOneById($waterSupplyFixture->getId());

        $this->assertEquals($waterSupplyFixture->getId(), $waterSupply->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $waterSupply = new WaterSupply();
        $waterSupply->setDate($this->getCurrentDateTime());
        $waterSupply->setAmount(250);
        $waterSupply->setUser($this->user);
        $this->repository->save($waterSupply);

        $this->assertNotNull($waterSupply->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $waterSupplyFixture = $this->fixtures->getReference('user_demo_water_supply_0_0');
        $id = $waterSupplyFixture->getId();
        $waterSupply = $this->repository->findOneById($id);
        $this->repository->remove($waterSupply);

        $waterSupply = $this->repository->findOneById($id);
        $this->assertNull($waterSupply);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
