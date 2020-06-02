<?php

namespace App\Tests\Unit\Services;

use App\Entity\User;
use App\Exception\InvalidDataException;
use App\Exception\InvalidPasswordException;
use App\Exception\NotAuthenticatedException;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Security;

class AuthorizationServiceTest extends TestCase
{
    use DateUtils;

    public function testGetCurrentUser()
    {
        $user = new User();
        $securityMock = $this->createMock(Security::class);
        $securityMock->method('getToken')->willReturn(new PreAuthenticatedToken($user, 'token', 'key'));

        $encoderMock = $this->createMock(UserPasswordEncoder::class);

        $service = new AuthorizationService($securityMock, $encoderMock);
        $result = $service->getCurrentUser();
        $this->assertEquals($user, $result);
    }

    public function testGetCurrentUserNoToken()
    {
        $securityMock = $this->createMock(Security::class);
        $securityMock->method('getToken')->willReturn(null);

        $encoderMock = $this->createMock(UserPasswordEncoder::class);

        $service = new AuthorizationService($securityMock, $encoderMock);

        $this->expectException(NotAuthenticatedException::class);

        $service->getCurrentUser();
    }

    public function testGetCurrentUserNotUserEntity()
    {
        $securityMock = $this->createMock(Security::class);
        $securityMock->method('getToken')->willReturn(new PreAuthenticatedToken(new InvalidDataException(), 'token', 'key'));

        $encoderMock = $this->createMock(UserPasswordEncoder::class);

        $service = new AuthorizationService($securityMock, $encoderMock);

        $this->expectException(NotAuthenticatedException::class);

        $service->getCurrentUser();
    }

    public function testWrongPassword()
    {
        $securityMock = $this->createMock(Security::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('isPasswordValid')->willReturn(false);

        $service = new AuthorizationService($securityMock, $encoderMock);

        $this->expectException(InvalidPasswordException::class);

        $service->isPasswordValid(new User(), 'password');
    }

    public function testCorrectPassword()
    {
        $securityMock = $this->createMock(Security::class);
        $encoderMock = $this->createMock(UserPasswordEncoder::class);
        $encoderMock->method('isPasswordValid')->willReturn(true);

        $service = new AuthorizationService($securityMock, $encoderMock);

        $this->assertNull($service->isPasswordValid(new User(), 'password'));
    }
}
