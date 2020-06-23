<?php

namespace App\Tests\Feature\Controller;

use App\Constant\MealTypes;
use App\DataFixtures\MealFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Meal;
use App\Entity\User;
use App\Repository\MealRepository;
use App\Repository\UserRepository;
use App\Repository\WeightRepository;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class MealControllerTest extends BaseTestCase
{
    use DateUtils;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            MealFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function testMealGetAll()
    {
        $mealRecords = $this->filterFixtures(function ($entity) {
            return $entity instanceof Meal
                && $entity->getUser()->getId() === $this->user->getId();
        });

        usort($mealRecords, function (Meal $a, Meal $b) {
            return $a->getDate() < $b->getDate();
        });

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findUserMeals')->with($this->user)->willReturn($mealRecords);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get('/meals');
        $content = $this->getContent();

        $expected = [
            'data' => array_map(function (Meal $meal) {
                return [
                    'id' => $meal->getId(),
                    'date' => $this->formatDate($meal->getDate()),
                    'type' => $meal->getType(),
                    'description' => $meal->getDescription(),
                ];
            }, $mealRecords)
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealGetSingle()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/meals/{$mealRecord->getId()}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $mealRecord->getId(),
                'date' => $this->formatDate($mealRecord->getDate()),
                'type' => $mealRecord->getType(),
                'description' => $mealRecord->getDescription(),
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealGetSingleWithUser()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/meals/{$mealRecord->getId()}?include=user");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $mealRecord->getId(),
                'date' => $this->formatDate($mealRecord->getDate()),
                'type' => $mealRecord->getType(),
                'description' => $mealRecord->getDescription(),
                'user' => [
                    'data' => [
                        'id' => $mealRecord->getUser()->getId(),
                        'username' => $mealRecord->getUser()->getUsername(),
                        'name' => $mealRecord->getUser()->getName(),
                        'lang' => $mealRecord->getUser()->getLang(),
                        'age' => $mealRecord->getUser()->getAge(),
                        'gender' => $mealRecord->getUser()->getGender(),
                        'height' => $mealRecord->getUser()->getHeight(),
                        'water_calculation' => $mealRecord->getUser()->getWaterCalculation(),
                        'water_amount' => $mealRecord->getUser()->getWaterAmount(),
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
    public function testMealGetSingleNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->get("/meals/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Meal with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealGetSingleNoPermission()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get("/meals/{$mealRecord->getId()}");
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
    public function testMealCreate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/meals",
            [
                'date' => '2020-02-02 12:12:12',
                'type' => MealTypes::LUNCH,
                'picture' => '/path',
                'description' => 'test'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'date' => '2020-02-02 12:12:12Z',
                'type' => MealTypes::LUNCH,
                'description' => 'test',
            ]
        ];

        unset($content['data']['id']);

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealCreateMissingDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/meals",
            [
                'type' => MealTypes::LUNCH
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
    public function testMealCreateInvalidDate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/meals",
            [
                'date' => '2020-02-02 12:12',
                'type' => MealTypes::LUNCH
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
    public function testMealCreateMissingType()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/meals",
            [
                'date' => '2020-02-02 12:12:12'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidDataException',
                'message' => [
                    'Type should not be blank'
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
    public function testMealCreateInvalidMealType()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/meals",
            [
                'date' => '2020-02-02 12:12',
                'type' => -10
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidMealTypeException',
                'message' => 'Meal Type -10 does not exist',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealUpdate()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $mealRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/meals/{$mealRecord->getId()}",
            [
                'date' => '2020-02-02 12:12:12',
                'type' => MealTypes::BREAKFAST,
                'description' => 'test-desc'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $mealRecord->getId(),
                'type' => MealTypes::BREAKFAST,
                'date' => '2020-02-02 12:12:12Z',
                'description' => 'test-desc',
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealUpdateNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();

        $this->post(
            "/meals/0",
            [
                'date' => '2020-02-02 12:12:12'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Meal with ID 0 was not found',
                'status' => 404
            ]
        ];

        $this->assertResponseStatusCode(404);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealUpdateNoPermission()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/meals/{$mealRecord->getId()}",
            [
                'date' => '2020-02-02 12:12:12'
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
    public function testMealUpdateInvalidMealType()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();

        $this->post(
            "/meals/{$mealRecord->getId()}",
            [
                'date' => '2020-02-02 12:12',
                'type' => -10
            ]
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidMealTypeException',
                'message' => 'Meal Type -10 does not exist',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealUpdateInvalidDate()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();

        $this->post(
            "/meals/{$mealRecord->getId()}",
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
    public function testMealDelete()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_0_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $mealRepositoryMock->expects($this->once())->method('remove')->willReturn(null);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);


        $this->login();
        $this->setCurrentUser($this->user);

        $id = $mealRecord->getId();

        $this->delete("/meals/{$id}");
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $mealRecord->getId(),
                'type' => $mealRecord->getType(),
                'date' => $this->formatDate($mealRecord->getDate()),
                'description' => $mealRecord->getDescription(),
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealDeleteNotFound()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with(0)->willReturn(null);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();

        $this->delete("/meals/0");
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'EntityNotFoundException',
                'message' => 'Meal with ID 0 was not found',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testMealDeleteNoPermission()
    {
        $mealRecord = $this->fixtures->getReference('user_demo_meal_1_0');

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);


        $mealRepositoryMock = $this->createMock(MealRepository::class);
        $mealRepositoryMock->expects($this->once())->method('findOneById')->with($mealRecord->getId())->willReturn($mealRecord);
        $this->client->getContainer()->set(MealRepository::class, $mealRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->delete("/meals/{$mealRecord->getId()}");
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
