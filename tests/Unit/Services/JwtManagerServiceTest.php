<?php

namespace App\Tests\Unit\Services;

use App\Entity\User;
use App\Services\JwtManagerService;
use App\Traits\DateUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class JwtManagerServiceTest extends TestCase
{
    use DateUtils;

    public function testJwtCreate()
    {
        $jwtManagerMock = $this->createMock(JWTManager::class);
        $jwtManagerMock->method('create')->willReturn('string');
        $service = new JwtManagerService($jwtManagerMock);
        $token = $service->create(new User());

        $this->assertIsString($token);
    }

    public function testJwtDecode()
    {
        $jwtManagerMock = $this->createMock(JWTManager::class);
        $jwtManagerMock->method('decode')->willReturn(['username' => 'username']);
        $service = new JwtManagerService($jwtManagerMock);
        $result = $service->decode(new PreAuthenticatedToken(new User(), 'token', 'key'));

        $this->assertArrayHasKey('username', $result);
    }
}
