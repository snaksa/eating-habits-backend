<?php

namespace App\Tests\Feature\Controller;

use App\Constant\MedicineFrequencies;
use App\DataFixtures\MedicineFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Medicine;
use App\Entity\User;
use App\Repository\MedicineRepository;
use App\Repository\UserRepository;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class MedicineControllerTest extends BaseTestCase
{
    use DateUtils;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            MedicineFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function testMedicineGetAll()
    {
        $medicineRecords = $this->filterFixtures(function ($entity) {
            return $entity instanceof Medicine
                && $entity->getUser()->getId() === $this->user->getId();
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $medicineRepository->expects($this->once())->method('findUserMedicines')->with($this->user)->willReturn($medicineRecords);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepository);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get('/medicines');
        $content = $this->getContent();

        $expected = [
            'data' => array_map(function (Medicine $medicine) {
                return [
                    'id' => $medicine->getId(),
                    'name' => $medicine->getName(),
                    'frequency' => $medicine->getFrequency(),
                ];
            }, $medicineRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineGetSingle()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/medicines/{$medicineRecord->getId()}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $medicineRecord->getId(),
                'name' => $medicineRecord->getName(),
                'frequency' => $medicineRecord->getFrequency(),
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineGetSingleWithUser()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/medicines/{$medicineRecord->getId()}?include=user");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $medicineRecord->getId(),
                'name' => $medicineRecord->getName(),
                'frequency' => $medicineRecord->getFrequency(),
                'user' => [
                    'data' => [
                        'id' => $medicineRecord->getUser()->getId(),
                        'username' => $medicineRecord->getUser()->getUsername(),
                        'name' => $medicineRecord->getUser()->getName(),
                        'lang' => $medicineRecord->getUser()->getLang(),
                        'age' => $medicineRecord->getUser()->getAge(),
                        'gender' => $medicineRecord->getUser()->getGender(),
                        'height' => $medicineRecord->getUser()->getHeight(),
                        'water_calculation' => $medicineRecord->getUser()->getWaterCalculation(),
                        'water_amount' => $medicineRecord->getUser()->getWaterAmount(),
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
    public function testMedicineGetSingleNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->get("/medicines/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Medicine with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineGetSingleNoPermission()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/medicines/{$medicineRecord->getId()}");
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
    public function testMedicineCreate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines",
            [
                'name' => 'name',
                'frequency' => MedicineFrequencies::EVERYDAY
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'name' => 'name',
                'frequency' => MedicineFrequencies::EVERYDAY,
            ]
        ];

        unset($content['data']['id']);

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineCreateMissingName()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines",
            [
                'frequency' => MedicineFrequencies::EVERYDAY
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'name should not be blank'
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
    public function testMedicineCreateInvalidFrequency()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/medicines",
            [
                'name' => 'name',
                'frequency' => -1,
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidMedicineFrequencyException',
                'message' => 'Medicine Frequency -1 does not exist',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineUpdate()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $medicineRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines/{$medicineRecord->getId()}",
            [
                'name' => 'name',
                'frequency' => MedicineFrequencies::ONCE
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $medicineRecord->getId(),
                'name' => 'name',
                'frequency' => MedicineFrequencies::ONCE
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineUpdateNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();

        $this->post(
            "/medicines/0",
            [
                'name' => 'name'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Medicine with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineUpdateNoPermission()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/medicines/{$medicineRecord->getId()}",
            [
                'name' => 'name',
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
    public function testMedicineDelete()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_1');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $medicineRepositoryMock->expects($this->once())->method('remove')->willReturn(null);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);


        $this->login();
        $this->setCurrentUser($this->user);

        $id = $medicineRecord->getId();

        $this->delete("/medicines/{$id}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $medicineRecord->getId(),
                'name' => $medicineRecord->getName(),
                'frequency' => $medicineRecord->getFrequency()
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


        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();

        $this->delete("/medicines/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Medicine with ID 0 was not found',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMedicineDeleteNoPermission()
    {
        $medicineRecord = $this->fixtures->getReference('user_demo_medicine_4');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $medicineRepositoryMock = $this->createMock(MedicineRepository::class);
        $medicineRepositoryMock->expects($this->once())->method('findOneById')->with($medicineRecord->getId())->willReturn($medicineRecord);
        $this->client->getContainer()->set(MedicineRepository::class, $medicineRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->delete("/medicines/{$medicineRecord->getId()}");
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
