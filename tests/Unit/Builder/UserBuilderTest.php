<?php

namespace App\Tests\Unit\Builder;

use App\Builder\UserBuilder;
use App\Entity\User;
use App\Exception\PasswordConfirmationException;
use App\Request\User\CreateUserRequest;
use App\Request\User\UpdateUserRequest;
use App\Services\JwtManagerService;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserBuilderTest extends TestCase
{
    use DateUtils;

    public function test_user_builder_create()
    {
        $user = (new User())->setId(1);

        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $jwtTokenManager = $this->createMock(JwtManagerService::class);

        $service = new UserBuilder($passwordEncoder, $jwtTokenManager);

        $user = $service
            ->create()
            ->setUser($user)
            ->build();

        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function test_user_builder_bind_create_request()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $passwordEncoder
            ->method('encodePassword')
            ->willReturn('123');


        $jwtTokenManager = $this->createMock(JwtManagerService::class);

        $user = (new User())->setId(1);

        $request = new CreateUserRequest(new Request());
        $request->username = 'test@gmail.com';
        $request->password = '123';
        $request->confirmPassword = '123';

        $service = new UserBuilder($passwordEncoder, $jwtTokenManager);

        $user = $service
            ->create()
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals('test@gmail.com', $user->getUsername());
    }

    public function test_user_builder_bind_create_request_invalid_password_confirmation_exception()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $jwtTokenManager = $this->createMock(JwtManagerService::class);

        $user = (new User())->setId(1);

        $request = new CreateUserRequest(new Request());
        $request->username = 'test@gmail.com';
        $request->password = '123';
        $request->confirmPassword = '456';

        $service = new UserBuilder($passwordEncoder, $jwtTokenManager);

        $this->expectException(PasswordConfirmationException::class);

        $service
            ->create()
            ->setUser($user)
            ->bind($request)
            ->build();
    }

    public function test_user_builder_bind_update_request()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $jwtTokenManager = $this->createMock(JwtManagerService::class);

        $user = (new User())->setId(1);

        $request = new UpdateUserRequest(new Request());
        $request->name = 'John Doe';
        $request->age = 27;
        $request->height = 190;
        $request->water_calculation = false;
        $request->water_amount = 1900;
        $request->gender = 2;
        $request->lang = 'es';

        $service = new UserBuilder($passwordEncoder, $jwtTokenManager);

        $user = $service
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals(27, $user->getAge());
        $this->assertEquals(190, $user->getHeight());
        $this->assertEquals(false, $user->getWaterCalculation());
        $this->assertEquals(1900, $user->getWaterAmount());
        $this->assertEquals(2, $user->getGender());
        $this->assertEquals('es', $user->getLang());
    }

    public function test_user_builder_get_api_key()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $jwtTokenManager = $this->createMock(JwtManagerService::class);
        $jwtTokenManager->method('create')
            ->willReturn('apiKey');

        $user = (new User())->setId(1);

        $service = new UserBuilder($passwordEncoder, $jwtTokenManager);

        $apiKey = $service
            ->setUser($user)
            ->getApiKey();

        $this->assertEquals('apiKey', $apiKey);
    }
}
