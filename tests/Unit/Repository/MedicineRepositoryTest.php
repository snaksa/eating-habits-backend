<?php

namespace App\Tests\Unit\Repository;

use App\Constant\MedicineFrequencies;
use App\DataFixtures\MedicineFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Medicine;
use App\Entity\User;
use App\Repository\MedicineRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class MedicineRepositoryTest extends BaseTestCase
{
    use DateUtils;

    private MedicineRepository $repository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager
            ->getRepository(Medicine::class);

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            MedicineFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_find_user_medicines(): void
    {
        $fixtureMedicines = $this->filterFixtures(function ($entity) {
            return $entity instanceof Medicine
                && $entity->getUser()->getId() === $this->user->getId();
        });

        $weights = $this->repository->findUserMedicines($this->user);

        $this->assertEquals(count($fixtureMedicines), count($weights));
    }

    /**
     * @test
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function can_find_one_by_id(): void
    {
        $medicineFixture = $this->fixtures->getReference('user_demo_medicine_1');
        $medicine = $this->repository->findOneById($medicineFixture->getId());

        $this->assertEquals($medicineFixture->getId(), $medicine->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_save(): void
    {
        $medicine = new Medicine();
        $medicine->setName('name');
        $medicine->setFrequency(MedicineFrequencies::PERIOD);
        $medicine->setUser($this->user);
        $this->repository->save($medicine);

        $this->assertNotNull($medicine->getId());
    }

    /**
     * @test
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function can_remove(): void
    {
        $fixtureMedicine = $this->fixtures->getReference('user_demo_medicine_1');
        $id = $fixtureMedicine->getId();
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
