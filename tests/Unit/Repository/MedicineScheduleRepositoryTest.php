<?php

namespace App\Tests\Unit\Repository;

use App\Constant\MedicineFrequencies;
use App\DataFixtures\MedicineFixtures;
use App\DataFixtures\MedicineScheduleFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Medicine;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use App\Repository\MedicineScheduleRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class MedicineScheduleRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private MedicineScheduleRepository $repository;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Medicine
     */
    private $medicine;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(MedicineSchedule::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            MedicineFixtures::class,
            MedicineScheduleFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
        $this->medicine = $this->fixtures->getReference('user_demo_medicine_1');
    }

    /**
     * @test
     */
    public function can_find_user_period_scheduled_medicines(): void
    {
        $fixtureMedicineSchedules = $this->filterFixtures(function ($entity) {
            return $entity instanceof MedicineSchedule
                && $entity->getMedicine()->getUser()->getId() === $this->user->getId()
                && $entity->getMedicine()->getFrequency() === MedicineFrequencies::PERIOD;
        });

        $weights = $this->repository->findUserPeriodMedicines($this->user);

        $this->assertSameSize($fixtureMedicineSchedules, $weights);
    }

    /**
     * @test
     */
    public function can_find_user_everyday_and_once_scheduled_medicines(): void
    {
        $startDate = $this->getCurrentDateTime()->setTime(0, 0, 0);
        $endDate = $this->getCurrentDateTime()->setTime(23, 59, 59);
        $fixtureMedicineSchedules = $this->filterFixtures(function ($entity) use ($startDate, $endDate) {
            return $entity instanceof MedicineSchedule
                && $entity->getMedicine()->getUser()->getId() === $this->user->getId()
                && (
                    ($entity->getMedicine()->getFrequency() === MedicineFrequencies::EVERYDAY && $entity->getIntakeTime() <= $endDate)
                    || ($entity->getIntakeTime() >= $startDate && $entity->getIntakeTime() <= $endDate && $entity->getMedicine()->getFrequency() === MedicineFrequencies::ONCE)
                );
        });

        $weights = $this->repository->findUserEverydayAndOnceScheduledMedicinesByDay($this->user, $startDate, $endDate);

        $this->assertSameSize($fixtureMedicineSchedules, $weights);
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $medicineScheduleFixture = $this->fixtures->getReference('user_demo_medicine_schedule_1');
        $medicineSchedule = $this->repository->findOneById($medicineScheduleFixture->getId());

        $this->assertEquals($medicineScheduleFixture->getId(), $medicineSchedule->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $medicineSchedule = new MedicineSchedule();
        $medicineSchedule->setPeriodSpan(100);
        $medicineSchedule->setIntakeTime($this->getCurrentDateTime());
        $medicineSchedule->setMedicine($this->medicine);
        $this->repository->save($medicineSchedule);

        $this->assertNotNull($medicineSchedule->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $id = $this->medicine->getId();
        $medicine = $this->repository->findOneById($id);
        $this->repository->remove($medicine);

        $medicine = $this->repository->findOneById($id);
        $this->assertNull($medicine);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
