<?php

namespace App\Tests\Unit\Repository;

use App\Constant\MealTypes;
use App\DataFixtures\MealFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Meal;
use App\Entity\User;
use App\Repository\MealRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class MealRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private MealRepository $repository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(Meal::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            MealFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_meals(): void
    {
        $fixtureMeals = $this->filterFixtures(function ($entity) {
            return $entity instanceof Meal
                && $entity->getUser()->getId() === $this->user->getId();
        });

        $meals = $this->repository->findUserMeals($this->user);

        $this->assertEquals(count($fixtureMeals), count($meals));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $fixtureMeal = $this->fixtures->getReference('user_demo_meal_0_0');
        $meal = $this->repository->findOneById($fixtureMeal->getId());

        $this->assertEquals($fixtureMeal->getId(), $meal->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $meal = new Meal();
        $meal->setDate($this->getCurrentDateTime());
        $meal->setDescription('desc');
        $meal->setPicture('picture');
        $meal->setType(MealTypes::BREAKFAST);
        $meal->setUser($this->user);
        $this->repository->save($meal);

        $this->assertNotNull($meal->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureMeal = $this->fixtures->getReference('user_demo_meal_0_0');
        $id = $fixtureMeal->getId();
        $meal = $this->repository->findOneById($id);
        $this->repository->remove($meal);

        $meal = $this->repository->findOneById($id);
        $this->assertNull($meal);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
