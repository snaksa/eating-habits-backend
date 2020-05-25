<?php

namespace App\Tests\Feature\Controller;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WeightFixtures;
use App\Entity\User;
use App\Entity\Weight;
use App\Repository\UserRepository;
use App\Repository\WeightRepository;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class WeightControllerTest extends BaseTestCase
{
    use DateUtils;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WeightFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function testWeightGetAll()
    {
        $weightRecords = $this->filterFixtures(function ($entity) {
            return $entity instanceof Weight
                && $entity->getUser()->getId() === $this->user->getId();
        });

        usort($weightRecords, function (Weight $a, Weight $b) {
            return $a->getDate() < $b->getDate();
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findUserWeights')->with($this->user)->willReturn($weightRecords);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get('/weights');
        $content = $this->getContent();

        $expected = [
            'data' => array_map(function (Weight $weight) {
                return [
                    'id' => $weight->getId(),
                    'weight' => $weight->getWeight(),
                    'date' => $this->formatDate($weight->getDate())
                ];
            }, $weightRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightGetSingle()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/weights/{$weightRecord->getId()}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $weightRecord->getId(),
                'weight' => $weightRecord->getWeight(),
                'date' => $this->formatDate($weightRecord->getDate())
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightGetSingleWithUser()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/weights/{$weightRecord->getId()}?include=user");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $weightRecord->getId(),
                'weight' => $weightRecord->getWeight(),
                'date' => $this->formatDate($weightRecord->getDate()),
                'user' => [
                    'data' => [
                        'id' => $weightRecord->getUser()->getId(),
                        'username' => $weightRecord->getUser()->getUsername(),
                        'name' => $weightRecord->getUser()->getName(),
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
    public function testWeightGetSingleNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->get("/weights/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Weight with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightGetSingleNoPermission()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/weights/{$weightRecord->getId()}");
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
    public function testWeightCreate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/weights",
            [
                'date' => '2020-02-02 12:12:12',
                'weight' => 78
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'weight' => 78,
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
    public function testWeightCreateMissingDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/weights",
            [
                'weight' => 78
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
    public function testWeightCreateInvalidDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/weights",
            [
                'date' => '2020-02-02 12:12',
                'weight' => 78
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
    public function testWeightCreateMissingWeight()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/weights",
            [
                'date' => '2020-02-02 12:12:12'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Weight should not be blank'
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
    public function testWeightCreateNegativeWeight()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/weights",
            [
                'date' => '2020-02-02 12:12',
                'weight' => -10
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Weight should be a positive number'
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
    public function testWeightUpdate()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $weightRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/weights/{$weightRecord->getId()}",
            [
                'date' => '2020-02-02 12:12:12',
                'weight' => 78
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $weightRecord->getId(),
                'weight' => 78,
                'date' => '2020-02-02 12:12:12Z'
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightUpdateNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();

        $this->post(
            "/weights/0",
            [
                'date' => '2020-02-02 12:12:12',
                'weight' => 78
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Weight with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightUpdateNoPermission()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/weights/{$weightRecord->getId()}",
            [
                'date' => '2020-02-02 12:12:12',
                'weight' => 78
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
    public function testWeightUpdateNegativeWeight()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/weights/{$weightRecord->getId()}",
            [
                'date' => '2020-02-02 12:12',
                'weight' => -10
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Weight should be a positive number'
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
    public function testWeightUpdateInvalidDate()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();

        $this->post(
            "/weights/{$weightRecord->getId()}",
            [
                'date' => '2020-02-02 12:12',
                'weight' => 78
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
    public function testWeightDelete()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $weightRepositoryMock->expects($this->once())->method('remove')->willReturn(null);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);


        $this->login();
        $this->setCurrentUser($this->user);

        $id = $weightRecord->getId();

        $this->delete("/weights/{$id}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $id,
                'weight' => $weightRecord->getWeight(),
                'date' => $this->formatDate($weightRecord->getDate())
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightDeleteNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();

        $this->delete("/weights/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Weight with ID 0 was not found',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testWeightDeleteNoPermission()
    {
        $weightRecord = $this->fixtures->getReference('user_demo_weight_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $weightRepositoryMock = $this->createMock(WeightRepository::class);
        $weightRepositoryMock->expects($this->once())->method('findOneById')->with($weightRecord->getId())->willReturn($weightRecord);
        $this->client->getContainer()->set(WeightRepository::class, $weightRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->delete("/weights/{$weightRecord->getId()}");
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
