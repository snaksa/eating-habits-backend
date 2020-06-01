<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\MedicineFixtures;
use App\DataFixtures\MedicineIntakeFixtures;
use App\DataFixtures\MedicineScheduleFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\MedicineIntake;
use App\Entity\User;
use App\Repository\MedicineIntakeRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class MedicineIntakeRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private MedicineIntakeRepository $repository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(MedicineIntake::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            MedicineFixtures::class,
            MedicineScheduleFixtures::class,
            MedicineIntakeFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_existing_intake(): void
    {
        $existingIntake = $this->fixtures->getReference('user_demo_medicine_intake_1');
        $startDate = $this->getCurrentDateTime()->setTime(0, 0, 0);
        $endDate = $this->getCurrentDateTime()->setTime(23, 59, 59);

        $fixtureMedicineSchedules = $this->filterFixtures(function ($entity) use ($existingIntake, $startDate, $endDate) {
            return $entity instanceof MedicineIntake
                && $entity->getMedicineSchedule()->getId() === $existingIntake->getMedicineSchedule()->getId()
                && $entity->getDate() >= $startDate && $entity->getDate() <= $endDate;
        });

        $weights = $this->repository->findExistingIntake($existingIntake->getMedicineSchedule(), $startDate, $endDate);

        $this->assertSameSize($fixtureMedicineSchedules, $weights);
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $medicineIntakeFixture = $this->fixtures->getReference('user_demo_medicine_intake_1');
        $medicineIntake = $this->repository->findOneById($medicineIntakeFixture->getId());

        $this->assertEquals($medicineIntakeFixture->getId(), $medicineIntake->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $medicineIntake = new MedicineIntake();
        $medicineIntake->setDate($this->getCurrentDateTime());
        $medicineIntake->setMedicineSchedule($this->fixtures->getReference('user_demo_medicine_schedule_1'));
        $this->repository->save($medicineIntake);

        $this->assertNotNull($medicineIntake->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureMedicineIntake = $this->fixtures->getReference('user_demo_medicine_intake_1');
        $id = $fixtureMedicineIntake->getId();
        $medicineIntake = $this->repository->findOneById($id);
        $this->repository->remove($medicineIntake);

        $medicineIntake = $this->repository->findOneById($id);
        $this->assertNull($medicineIntake);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
