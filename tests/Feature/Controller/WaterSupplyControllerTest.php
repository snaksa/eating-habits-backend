<?php

namespace App\Tests\Feature\Controller;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WaterSupplyFixtures;
use App\Entity\User;
use App\Entity\WaterSupply;
use App\Repository\UserRepository;
use App\Repository\WaterSupplyRepository;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class WaterSupplyControllerTest extends BaseTestCase
{
    use DateUtils;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WaterSupplyFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function testWaterSupplyGetAll()
    {
        $waterSupplyRecords = $this->filterFixtures(function ($entity) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $this->user->getId();
        });

        usort($waterSupplyRecords, function (WaterSupply $a, WaterSupply $b) {
            return $a->getDate() > $b->getDate();
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepository = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepository->expects($this->once())->method('findUserWaterSupplies')->with($this->user)->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepository);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get('/water-supplies');
        $content = $this->getContent();

        $expected = [
            'data' => array_map(function (WaterSupply $waterSupply) {
                return [
                    'id' => $waterSupply->getId(),
                    'amount' => $waterSupply->getAmount(),
                    'date' => $this->formatDate($waterSupply->getDate())
                ];
            }, $waterSupplyRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyGetSingle()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/water-supplies/{$waterSupplyRecord->getId()}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $waterSupplyRecord->getId(),
                'amount' => $waterSupplyRecord->getAmount(),
                'date' => $this->formatDate($waterSupplyRecord->getDate())
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyGetSingleWithUser()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/water-supplies/{$waterSupplyRecord->getId()}?include=user");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $waterSupplyRecord->getId(),
                'amount' => $waterSupplyRecord->getAmount(),
                'date' => $this->formatDate($waterSupplyRecord->getDate()),
                'user' => [
                    'data' => [
                        'id' => $waterSupplyRecord->getUser()->getId(),
                        'username' => $waterSupplyRecord->getUser()->getUsername(),
                        'name' => $waterSupplyRecord->getUser()->getName(),
                        'lang' => $waterSupplyRecord->getUser()->getLang(),
                        'age' => $waterSupplyRecord->getUser()->getAge(),
                        'gender' => $waterSupplyRecord->getUser()->getGender(),
                        'height' => $waterSupplyRecord->getUser()->getHeight(),
                        'water_calculation' => $waterSupplyRecord->getUser()->getWaterCalculation(),
                        'water_amount' => $waterSupplyRecord->getUser()->getWaterAmount(),
                    ]
                ]
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyGetSingleNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->get("/water-supplies/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'WaterSupply with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyGetSingleNoPermission()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/water-supplies/{$waterSupplyRecord->getId()}");
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
    public function testWaterSupplyCreate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/water-supplies",
            [
                'date' => '2020-02-02 12:12:12',
                'amount' => 250
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'amount' => 250,
                'date' => '2020-02-02 12:12:12Z'
            ]
        ];

        unset($content['data']['id']);

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyCreateMissingDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies",
            [
                'amount' => 250
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Date should not be blank'
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
    public function testWaterSupplyCreateInvalidDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies",
            [
                'date' => '2020-02-02 12:12',
                'amount' => 250
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
    public function testWaterSupplyCreateMissingAmount()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies",
            [
                'date' => '2020-02-02 12:12:12'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Amount should not be blank'
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
    public function testWaterSupplyCreateNegativeAmount()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies",
            [
                'date' => '2020-02-02 12:12',
                'amount' => -10
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Amount should be a positive number'
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
    public function testWaterSupplyUpdate()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $waterSupplyRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/water-supplies/{$waterSupplyRecord->getId()}",
            [
                'date' => '2020-02-02 12:12:12',
                'amount' => 250
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $waterSupplyRecord->getId(),
                'amount' => 250,
                'date' => '2020-02-02 12:12:12Z'
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyUpdateNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies/0",
            [
                'date' => '2020-02-02 12:12:12',
                'amount' => 250
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'WaterSupply with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyUpdateNoPermission()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/water-supplies/{$waterSupplyRecord->getId()}",
            [
                'date' => '2020-02-02 12:12:12',
                'amount' => 250
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
    public function testWaterSupplyUpdateNegativeAmount()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies/{$waterSupplyRecord->getId()}",
            [
                'date' => '2020-02-02 12:12',
                'amount' => -250
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Amount should be a positive number'
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
    public function testWaterSupplyUpdateInvalidDate()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();

        $this->post(
            "/water-supplies/{$waterSupplyRecord->getId()}",
            [
                'date' => '2020-02-02 12:12',
                'amount' => 250
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
    public function testWaterSupplyDelete()
    {
        $waterSuppliesRecord = $this->fixtures->getReference('user_demo_water_supply_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSuppliesRecord->getId())->willReturn($waterSuppliesRecord);
        $waterSupplyRepositoryMock->expects($this->once())->method('remove')->willReturn(null);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);


        $this->login();
        $this->setCurrentUser($this->user);

        $id = $waterSuppliesRecord->getId();

        $this->delete("/water-supplies/{$id}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $id,
                'amount' => $waterSuppliesRecord->getAmount(),
                'date' => $this->formatDate($waterSuppliesRecord->getDate())
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyDeleteNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();

        $this->delete("/water-supplies/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'WaterSupply with ID 0 was not found',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyDeleteNoPermission()
    {
        $waterSupplyRecord = $this->fixtures->getReference('user_demo_water_supply_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findOneById')->with($waterSupplyRecord->getId())->willReturn($waterSupplyRecord);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->delete("/water-supplies/{$waterSupplyRecord->getId()}");
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
    public function testWaterSupplyGetByDay()
    {
        $user = $this->fixtures->getReference('user_demo3');

        $startDate = (new \DateTime())->setDate(2019, 12, 11)->setTime(21, 0, 0);
        $endDate = (new \DateTime())->setDate(2019, 12, 12)->setTime(21, 0, 0);

        $waterSupplyRecords = $this->filterFixtures(function ($entity) use ($user, $startDate, $endDate) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $user->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() < $endDate;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findUserWaterSuppliesByDay')->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($user);

        $this->get("/water-supplies/byDay?date=2019-12-11 21:00:00");
        $content = $this->getContent();

        $sum = 0;
        foreach ($waterSupplyRecords as $record) {
            $sum += $record->getAmount();
        }

        $expected = [
            'data' => array_map(function (WaterSupply $waterSupply) {
                return [
                    'id' => $waterSupply->getId(),
                    'amount' => $waterSupply->getAmount(),
                    'date' => $this->formatDate($waterSupply->getDate())
                ];
            }, $waterSupplyRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
        $this->assertEquals(1000, $sum);
    }

    /**
     * @test
     */
    public function testWaterSupplyGetByDayPrevious()
    {
        $user = $this->fixtures->getReference('user_demo3');

        $startDate = (new \DateTime())->setDate(2019, 12, 10)->setTime(21, 0, 0);
        $endDate = (new \DateTime())->setDate(2019, 12, 11)->setTime(21, 0, 0);

        $waterSupplyRecords = $this->filterFixtures(function ($entity) use ($user, $startDate, $endDate) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $user->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() < $endDate;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findUserWaterSuppliesByDay')->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($user);

        $this->get("/water-supplies/byDay?date=2019-12-10 21:00:00");
        $content = $this->getContent();

        $sum = 0;
        foreach ($waterSupplyRecords as $record) {
            $sum += $record->getAmount();
        }

        $expected = [
            'data' => array_map(function (WaterSupply $waterSupply) {
                return [
                    'id' => $waterSupply->getId(),
                    'amount' => $waterSupply->getAmount(),
                    'date' => $this->formatDate($waterSupply->getDate())
                ];
            }, $waterSupplyRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
        $this->assertEquals(250, $sum);
    }

    /**
     * @test
     */
    public function testWaterSupplyGetByDayToday()
    {
        $user = $this->fixtures->getReference('user_demo3');

        $date = $this->getCurrentDateTime()->setTime(21, 0, 0);
        $startDate = (clone $date)->modify('- 1 days');
        $endDate = (clone $startDate)->modify('+ 1 days');

        $waterSupplyRecords = $this->filterFixtures(function ($entity) use ($user, $startDate, $endDate) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $user->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() < $endDate;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findUserWaterSuppliesByDay')->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($user);

        $this->get("/water-supplies/byDay");
        $content = $this->getContent();

        $sum = 0;
        foreach ($waterSupplyRecords as $record) {
            $sum += $record->getAmount();
        }

        $expected = [
            'data' => array_map(function (WaterSupply $waterSupply) {
                return [
                    'id' => $waterSupply->getId(),
                    'amount' => $waterSupply->getAmount(),
                    'date' => $this->formatDate($waterSupply->getDate())
                ];
            }, $waterSupplyRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
        $this->assertEquals(250, $sum);
    }

    /**
     * @test
     */
    public function testWaterSupplyGroupByDays()
    {
        $user = $this->fixtures->getReference('user_demo3');

        $startDate = (new \DateTime())->setDate(2019, 12, 10)->setTime(21, 0, 0);
        $endDate = (new \DateTime())->setDate(2019, 12, 12)->setTime(21, 0, 0);

        $waterSupplyRecords = $this->filterFixtures(function ($entity) use ($user, $startDate, $endDate) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $user->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() < $endDate;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findUserWaterSuppliesGroupByDay')->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($user);

        $this->get("/water-supplies/groupByDays?startDate=2019-12-10 21:00:00&endDate=2019-12-17 21:00:00");
        $content = $this->getContent();

        $expected = [
            'data' => [
                [
                    'date' => '2019-12-10 21:00:00Z',
                    'amount' => 250
                ],
                [
                    'date' => '2019-12-11 21:00:00Z',
                    'amount' => 1000
                ],
                [
                    'date' => '2019-12-12 21:00:00Z',
                    'amount' => 0
                ],
                [
                    'date' => '2019-12-13 21:00:00Z',
                    'amount' => 0
                ],
                [
                    'date' => '2019-12-14 21:00:00Z',
                    'amount' => 0
                ],
                [
                    'date' => '2019-12-15 21:00:00Z',
                    'amount' => 0
                ],
                [
                    'date' => '2019-12-16 21:00:00Z',
                    'amount' => 0
                ],
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyGroupByDaysToday()
    {
        $user = $this->fixtures->getReference('user_demo3');

        $date = $this->getCurrentDateTime()->setTime(21, 0, 0);
        $startDate = (clone $date)->modify('- 7 days');
        $endDate = (clone $startDate)->modify('+ 7 days');

        $waterSupplyRecords = $this->filterFixtures(function ($entity) use ($user, $startDate, $endDate) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $user->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() < $endDate;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findUserWaterSuppliesGroupByDay')->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($user);

        $this->get("/water-supplies/groupByDays");
        $content = $this->getContent();

        $expected = [
            'data' => [
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 6 days')),
                    'amount' => 0
                ],
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 5 days')),
                    'amount' => 0
                ],
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 4 days')),
                    'amount' => 0
                ],
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 3 days')),
                    'amount' => 0
                ],
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 2 days')),
                    'amount' => 0
                ],
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 1 days')),
                    'amount' => 500
                ],
                [
                    'date' => $this->formatDate($this->getCurrentDateTime()->setTime(0, 0, 0)),
                    'amount' => 250
                ],
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWaterSupplyGroupByDaysLastTwoDays()
    {
        $user = $this->fixtures->getReference('user_demo3');

        $date = $this->getCurrentDateTime()->setTime(21, 0, 0);
        $startDate = (clone $date)->modify('- 2 days');
        $endDate = clone $date;

        $waterSupplyRecords = $this->filterFixtures(function ($entity) use ($user, $startDate, $endDate) {
            return $entity instanceof WaterSupply
                && $entity->getUser()->getId() === $user->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() < $endDate;
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $waterSupplyRepositoryMock = $this->createMock(WaterSupplyRepository::class);
        $waterSupplyRepositoryMock->expects($this->once())->method('findUserWaterSuppliesGroupByDay')->willReturn($waterSupplyRecords);
        $this->client->getContainer()->set(WaterSupplyRepository::class, $waterSupplyRepositoryMock);

        $this->login();
        $this->setCurrentUser($user);

        $this->get("/water-supplies/groupByDays?startDate={$startDate->format('Y-m-d H:i:s')}&endDate={$endDate->format('Y-m-d H:i:s')}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                [
                    'date' => $this->formatDate($startDate),
                    'amount' => 500
                ],
                [
                    'date' => $this->formatDate($startDate->modify('+ 1 days')),
                    'amount' => 250
                ],
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }
}
