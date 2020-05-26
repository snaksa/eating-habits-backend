<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WeightFixtures;
use App\Entity\User;
use App\Entity\Weight;
use App\Repository\WeightRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class WeightRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private WeightRepository $repository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(Weight::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WeightFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_weights(): void
    {
        $fixtureWeights = $this->filterFixtures(function ($entity) {
            return $entity instanceof Weight
                && $entity->getUser()->getId() === $this->user->getId();
        });

        $weights = $this->repository->findUserWeights($this->user);

        $this->assertEquals(count($fixtureWeights), count($weights));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $weightFixture = $this->fixtures->getReference('user_demo_weight_0_0');
        $weight = $this->repository->findOneById($weightFixture->getId());

        $this->assertEquals($weightFixture->getId(), $weight->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $weight = new Weight();
        $weight->setDate($this->getCurrentDateTime());
        $weight->setWeight(80);
        $weight->setUser($this->user);
        $this->repository->save($weight);

        $this->assertNotNull($weight->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureWeight = $this->fixtures->getReference('user_demo_weight_0_0');
        $id = $fixtureWeight->getId();
        $weight = $this->repository->findOneById($id);
        $this->repository->remove($weight);

        $weight = $this->repository->findOneById($id);
        $this->assertNull($weight);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
