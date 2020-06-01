<?php

namespace App\Tests\Feature\Controller;

use App\DataFixtures\MedicineFixtures;
use App\DataFixtures\MedicineIntakeFixtures;
use App\DataFixtures\MedicineScheduleFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\MedicineIntake;
use App\Entity\User;
use App\Repository\MedicineIntakeRepository;
use App\Repository\MedicineScheduleRepository;
use App\Repository\UserRepository;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class MedicineIntakeControllerTest extends BaseTestCase
{
    use DateUtils;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

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
    public function testMedicineIntakeCreate()
    {
        $date = $this->getCurrentDateTime();
        $medicineSchedule = $this->fixtures->getReference('user_demo_medicine_schedule_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicineSchedule);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);


        $this->post(
            "/medicines-intake",
            [
                'date' => $date->modify('+ 1 days')->format('Y-m-d H:i:s'),
                'medicineScheduleId' => $medicineSchedule->getId()
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'date' => $this->formatDate($date),
            ]
        ];

        unset($content['data']['id']);

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeCreateMissingDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-intake",
            [
                'medicineScheduleId' => 1
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'date should not be blank'
                ],
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeCreateInvalidDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-intake",
            [
                'date' => '2020-02-02',
                'medicineScheduleId' => 1
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDateException',
                'message' => 'Date is not valid',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeCreateMissingMedicineScheduleId()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-intake",
            [
                'date' => '2020-02-02 12:12:12'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'medicineScheduleId should not be blank'
                ],
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeCreateMedicineScheduleNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn(null);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-intake",
            [
                'date' => '2020-02-02 12:12:12',
                'medicineScheduleId' => -1
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => 'MedicineSchedule with ID -1 does not exist',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeCreateAlreadyExists()
    {
        $medicineSchedule = $this->fixtures->getReference('user_demo_medicine_schedule_1');
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicineSchedule);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);


        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findExistingIntake')->willReturn(new MedicineIntake());
        $this->client->getContainer()->set(MedicineIntakeRepository::class, $medicineIntakeRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-intake",
            [
                'date' => '2020-02-02 12:12:12',
                'medicineScheduleId' => $medicineSchedule->getId()
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => 'Intake record already exists',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeCreateNoPermission()
    {
        $date = $this->getCurrentDateTime();
        $medicineSchedule = $this->fixtures->getReference('user_demo_medicine_schedule_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicineSchedule);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findExistingIntake')->willReturn(null);
        $this->client->getContainer()->set(MedicineIntakeRepository::class, $medicineIntakeRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);


        $this->post(
            "/medicines-intake",
            [
                'date' => $date->format('Y-m-d H:i:s'),
                'medicineScheduleId' => $medicineSchedule->getId()
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'NotAuthorizedException',
                'message' => 'You do not have permissions to set intake for this schedule',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeDelete()
    {
        $medicineIntakeRecord = $this->fixtures->getReference('user_demo_medicine_intake_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findOneById')->with($medicineIntakeRecord->getId())->willReturn($medicineIntakeRecord);
        $medicineIntakeRepositoryMock->expects($this->once())->method('remove')->willReturn(null);
        $this->client->getContainer()->set(MedicineIntakeRepository::class, $medicineIntakeRepositoryMock);


        $this->login();
        $this->setCurrentUser($this->user);

        $id = $medicineIntakeRecord->getId();

        $this->delete("/medicines-intake/{$id}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $id,
                'date' => $this->formatDate($medicineIntakeRecord->getDate()),
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineDeleteNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MedicineIntakeRepository::class, $medicineIntakeRepositoryMock);

        $this->login();

        $this->delete("/medicines-intake/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'MedicineIntake with ID 0 was not found',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineIntakeDeleteNoPermission()
    {
        $medicineIntakeRecord = $this->fixtures->getReference('user_demo_medicine_intake_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findOneById')->with($medicineIntakeRecord->getId())->willReturn($medicineIntakeRecord);
        $this->client->getContainer()->set(MedicineIntakeRepository::class, $medicineIntakeRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->delete("/medicines-intake/{$medicineIntakeRecord->getId()}");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'NotAuthorizedException',
                'message' => 'You do not have permissions to access this resource',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }
}
