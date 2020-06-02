<?php

namespace App\Tests\Feature\Controller;

use App\Constant\MedicineFrequencies;
use App\DataFixtures\MedicineFixtures;
use App\DataFixtures\MedicineScheduleFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use App\Repository\MedicineRepository;
use App\Repository\MedicineScheduleRepository;
use App\Repository\UserRepository;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class MedicineScheduleControllerTest extends BaseTestCase
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
            MedicineScheduleFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function testMedicineScheduleGetByDay()
    {
        $startDate = $this->getCurrentDateTime()->setTime(0, 0, 0);
        $endDate = $this->getCurrentDateTime()->setTime(23, 59, 59);
        $scheduledMedicines = $this->filterFixtures(function ($entity) use ($startDate, $endDate) {
            return $entity instanceof MedicineSchedule
                && $entity->getMedicine()->getUser()->getId() === $this->user->getId()
                && (
                    ($entity->getMedicine()->getFrequency() === MedicineFrequencies::EVERYDAY && $entity->getIntakeTime() <= $endDate)
                    || ($entity->getIntakeTime() >= $startDate && $entity->getIntakeTime() <= $endDate && $entity->getMedicine()->getFrequency() === MedicineFrequencies::ONCE)
                );
        });

        $fixtureMedicineSchedulesPeriod = $this->filterFixtures(function ($entity) {
            return $entity instanceof MedicineSchedule
                && $entity->getMedicine()->getUser()->getId() === $this->user->getId()
                && $entity->getMedicine()->getFrequency() === MedicineFrequencies::PERIOD;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findUserEverydayAndOnceScheduledMedicinesByDay')->willReturn($scheduledMedicines);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findUserPeriodMedicines')->willReturn($fixtureMedicineSchedulesPeriod);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        foreach ($fixtureMedicineSchedulesPeriod as $period) {
            $medicineStartDate = $period->getIntakeTime();
            while ($medicineStartDate < $endDate) {
                if ($startDate <= $medicineStartDate) {
                    $period->setIntakeTime($medicineStartDate);
                    $scheduledMedicines[] = $period;
                    break;
                }
                $medicineStartDate->add(new \DateInterval("PT{$period->getPeriodSpan()}S"));
            }
        }

        usort($scheduledMedicines, function (MedicineSchedule $a, MedicineSchedule $b) {
            return $a->getIntakeTime()->format('H:i:s') > $b->getIntakeTime()->format('H:i:s');
        });

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get('/medicines-schedule/byDay');
        $content = $this->getContent();

        $expected = [
            'data' => array_map(function (MedicineSchedule $medicineSchedule) {
                return [
                    'id' => $medicineSchedule->getId(),
                    'intakeTime' => $this->formatDate($medicineSchedule->getIntakeTime()),
                    'periodSpan' => $medicineSchedule->getPeriodSpan()
                ];
            }, $scheduledMedicines)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleGetByDayWithDate()
    {
        $startDate = $this->getCurrentDateTime()->modify('+ 1 days')->setTime(0, 0, 0);
        $endDate = $this->getCurrentDateTime()->modify('+ 1 days')->setTime(23, 59, 59);
        $scheduledMedicines = $this->filterFixtures(function ($entity) use ($startDate, $endDate) {
            return $entity instanceof MedicineSchedule
                && $entity->getMedicine()->getUser()->getId() === $this->user->getId()
                && (
                    ($entity->getMedicine()->getFrequency() === MedicineFrequencies::EVERYDAY && $entity->getIntakeTime() <= $endDate)
                    || ($entity->getIntakeTime() >= $startDate && $entity->getIntakeTime() <= $endDate && $entity->getMedicine()->getFrequency() === MedicineFrequencies::ONCE)
                );
        });

        $fixtureMedicineSchedulesPeriod = $this->filterFixtures(function ($entity) {
            return $entity instanceof MedicineSchedule
                && $entity->getMedicine()->getUser()->getId() === $this->user->getId()
                && $entity->getMedicine()->getFrequency() === MedicineFrequencies::PERIOD;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findUserEverydayAndOnceScheduledMedicinesByDay')->willReturn($scheduledMedicines);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findUserPeriodMedicines')->willReturn($fixtureMedicineSchedulesPeriod);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/medicines-schedule/byDay?date={$startDate->format('Y-m-d H:i:s')}");
        $content = $this->getContent();

        foreach ($fixtureMedicineSchedulesPeriod as $period) {
            $medicineStartDate = $period->getIntakeTime();
            while ($medicineStartDate < $endDate) {
                if ($startDate <= $medicineStartDate) {
                    $period->setIntakeTime($medicineStartDate);
                    $scheduledMedicines[] = $period;
                    break;
                }
                $medicineStartDate = clone $medicineStartDate->add(new \DateInterval("PT{$period->getPeriodSpan()}S"));
            }
        }

        usort($scheduledMedicines, function (MedicineSchedule $a, MedicineSchedule $b) {
            return $a->getIntakeTime()->format('H:i:s') > $b->getIntakeTime()->format('H:i:s');
        });

        $expected = [
            'data' => array_map(function (MedicineSchedule $medicineSchedule) {
                return [
                    'id' => $medicineSchedule->getId(),
                    'intakeTime' => $this->formatDate($medicineSchedule->getIntakeTime()),
                    'periodSpan' => $medicineSchedule->getPeriodSpan()
                ];
            }, $scheduledMedicines)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }


    /**
     * @test
     */
    public function testMedicineScheduleCreate()
    {
        $date = $this->getCurrentDateTime();
        $medicine = $this->fixtures->getReference('user_demo_medicine_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicine);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines-schedule",
            [
                'medicineId' => $medicine->getId(),
                'intakeTime' => $date->format('Y-m-d H:i:s'),
                'periodSpan' => 100
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'intakeTime' => $this->formatDate($date),
                'periodSpan' => 100
            ]
        ];

        unset($content['data']['id']);

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleCreateMissingIntakeTime()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-schedule",
            [
                'medicineId' => 1,
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'intakeTime should not be blank'
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
    public function testMedicineScheduleCreateInvalidIntakeTime()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-schedule",
            [
                'intakeTime' => '2020-02-02',
                'medicineId' => 1
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDateException',
                'message' => 'Intake time is not valid',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleCreateMissingMedicineId()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-schedule",
            [
                'intakeTime' => '2020-02-02 12:12:12',
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'medicineId should not be blank'
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
    public function testMedicineScheduleCreateMedicineNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->willReturn(null);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-schedule",
            [
                'intakeTime' => '2020-02-02 12:12:12',
                'medicineId' => -1
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => 'Medicine with ID -1 does not exist',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleCreatePeriodWithoutPeriodSpan()
    {
        $medicine = $this->fixtures->getReference('user_demo_medicine_3');
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicine);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-schedule",
            [
                'intakeTime' => '2020-02-02 12:12:12',
                'medicineId' => $medicine->getId()
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => 'Medicine frequency is periodic and periodSpan should be specified',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleCreateNoPermission()
    {
        $medicine = $this->fixtures->getReference('user_demo_medicine_4');
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicine);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines-schedule",
            [
                'intakeTime' => '2020-02-02 12:12:12',
                'medicineId' => $medicine->getId()
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'NotAuthorizedException',
                'message' => 'You do not have permissions to set schedule for this medicine',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleUpdate()
    {
        $date = $this->getCurrentDateTime();
        $medicineScheduleRecord = $this->fixtures->getReference('user_demo_medicine_schedule_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->with($medicineScheduleRecord->getId())->willReturn($medicineScheduleRecord);
        $medicineScheduleRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines-schedule/{$medicineScheduleRecord->getId()}",
            [
                'periodSpan' => 100,
                'intakeTime' => $date->format('Y-m-d H:i:s')
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $medicineScheduleRecord->getId(),
                'periodSpan' => 100,
                'intakeTime' => $this->formatDate($date)
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleUpdateNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn(null);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();

        $this->post(
            "/medicines-schedule/0",
            [
                'periodSpan' => 100
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'MedicineSchedule with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleUpdateNoPermission()
    {
        $medicineScheduleRecord = $this->fixtures->getReference('user_demo_medicine_schedule_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->with($medicineScheduleRecord->getId())->willReturn($medicineScheduleRecord);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines-schedule/{$medicineScheduleRecord->getId()}",
            [
                'periodSpan' => 100,
            ]
        );
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

    /**
     * @test
     */
    public function testMedicineScheduleDelete()
    {
        $medicineScheduleRecord = $this->fixtures->getReference('user_demo_medicine_schedule_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->with($medicineScheduleRecord->getId())->willReturn($medicineScheduleRecord);
        $medicineScheduleRepositoryMock->expects($this->once())->method('remove')->willReturn(null);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);


        $this->login();
        $this->setCurrentUser($this->user);

        $id = $medicineScheduleRecord->getId();

        $this->delete("/medicines-schedule/{$id}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $medicineScheduleRecord->getId(),
                'intakeTime' => $this->formatDate($medicineScheduleRecord->getIntakeTime()),
                'periodSpan' => $medicineScheduleRecord->getPeriodSpan()
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleDeleteNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn(null);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();

        $this->delete("/medicines-schedule/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'MedicineSchedule with ID 0 was not found',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineScheduleDeleteNoPermission()
    {
        $medicineScheduleRecord = $this->fixtures->getReference('user_demo_medicine_schedule_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->with($medicineScheduleRecord->getId())->willReturn($medicineScheduleRecord);
        $this->client->getContainer()->set(MedicineScheduleRepository::class, $medicineScheduleRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->delete("/medicines-schedule/{$medicineScheduleRecord->getId()}");
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
