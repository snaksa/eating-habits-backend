<?php

namespace App\Tests\Feature\Controller;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\WeightFixtures;
use App\Entity\User;
use App\Entity\Weight;
use App\Exception\InvalidPasswordException;
use App\Repository\UserRepository;
use App\Repository\WeightRepository;
use App\Services\AuthorizationService;
use App\Services\JwtManagerService;
use App\Traits\DateUtils;
use App\Tests\Feature\BaseTestCase;

class UserControllerTest extends BaseTestCase
{
    use DateUtils;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function testUserRegister()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn(null);
        $userRepositoryMock->expects($this->once())->method('save')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $jwtManagerMock = $this->createMock(JwtManagerService::class);
        $jwtManagerMock->expects($this->once())->method('create')->willReturn('token');
        $this->client->getContainer()->set(JwtManagerService::class, $jwtManagerMock);

        $this->post(
            '/users',
            [
                'username' => 'test@gmail.com',
                'password' => '123456',
                'confirmPassword' => '123456',
            ],
            true
        );
        $content = $this->getContent();

        $this->assertResponseStatusCode(200);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('token', $content['data']);
        $this->assertArrayHasKey('expiresIn', $content['data']);
    }

    /**
     * @test
     */
    public function testUserRegisterWithExistingUsername()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->post(
            '/users',
            [
                'username' => $this->user->getUsername(),
                'password' => '123456',
                'confirmPassword' => '123456',
            ],
            true
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'UserAlreadyExistsException',
                'message' => 'User with this email already exists',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testUserLogin()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->with(['username' => $this->user->getUsername()])->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isPasswordValid')->willReturn(null);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $jwtManagerMock = $this->createMock(JwtManagerService::class);
        $jwtManagerMock->expects($this->once())->method('create')->willReturn('token');
        $this->client->getContainer()->set(JwtManagerService::class, $jwtManagerMock);

        $this->post(
            '/users/login',
            [
                'username' => $this->user->getUsername(),
                'password' => '123456',
            ],
            true
        );
        $content = $this->getContent();

        $this->assertResponseStatusCode(200);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('token', $content['data']);
        $this->assertArrayHasKey('expiresIn', $content['data']);
    }

    /**
     * @test
     */
    public function testUserLoginFailure()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->with(['username' => $this->user->getUsername()])->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isPasswordValid')->willThrowException(new InvalidPasswordException('Wrong username or password'));
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->post(
            '/users/login',
            [
                'username' => $this->user->getUsername(),
                'password' => '123456',
            ],
            true
        );
        $content = $this->getContent();

        $expected = [
            'error' => [
                'type' => 'InvalidPasswordException',
                'message' => 'Wrong username or password',
                'status' => 400
            ]
        ];

        $this->assertResponseStatusCode(400);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testUserMe()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->get('/users/me');
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $this->user->getId(),
                'username' => $this->user->getUsername(),
                'name' => $this->user->getName()
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testUserUpdate()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($this->user);
        $userRepositoryMock->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser($this->user);

        $this->post(
            "/users/{$this->user->getId()}",
            [
                'name' => 'test name'
            ]
        );
        $content = $this->getContent();

        $expected = [
            'data' => [
                'id' => $this->user->getId(),
                'username' => $this->user->getUsername(),
                'name' => 'test name'
            ]
        ];

        $this->assertResponseStatusCode(200);
        $this->assertEquals($expected, $content);
    }

    /**
     * @test
     */
    public function testUserUpdateNoPermission()
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->expects($this->once())->method('findOneBy')->willReturn((new User())->setId(-1));
        $this->client->getContainer()->set(UserRepository::class, $userRepositoryMock);

        $this->login();
        $this->setCurrentUser((new User())->setId(-1));

        $this->post(
            "/users/{$this->user->getId()}",
            [
                'name' => 'test name'
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
}
