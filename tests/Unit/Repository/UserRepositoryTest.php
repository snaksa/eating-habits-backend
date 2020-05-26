<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WeightFixtures;
use App\Entity\User;
use App\Entity\Weight;
use App\Repository\UserRepository;
use App\Repository\WeightRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private UserRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(User::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
        ])->getReferenceRepository();
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $userFixture = $this->fixtures->getReference('user_demo');
        $user = $this->repository->findOneById($userFixture->getId());

        $this->assertEquals($userFixture->getId(), $user->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setUsername('test@gmail.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('123');
        $this->repository->save($user);

        $this->assertNotNull($user->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureUser = $this->fixtures->getReference('user_demo');
        $id = $fixtureUser->getId();
        $user = $this->repository->findOneById($id);
        $this->repository->remove($user);

        $user = $this->repository->findOneById($id);
        $this->assertNull($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
