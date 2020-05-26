<?php

namespace App\Tests\Feature;

use App\Entity\User;
use App\Services\AuthorizationService;
use App\Services\JwtManagerService;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class BaseTestCase extends WebTestCase
{
    use FixturesTrait {
        tearDown as protected fixturesTearDown;
    }

    protected ?ReferenceRepository $fixtures = null;
    protected ?array $initializedReferences = null;
    protected ?KernelBrowser $client = null;

    protected function login()
    {
        $mock = $this->createMock(JwtManagerService::class);
        $mock->expects($this->once())->method('decode')->willReturn(['username' => 'username']);
        $this->client->getContainer()->set(JwtManagerService::class, $mock);
    }

    public function getReferences()
    {
        if ($this->initializedReferences === null) {
            foreach ($this->fixtures->getReferences() as $index => $object) {
                $this->initializedReferences[$index] = $this->fixtures->getReference($index);
            }
        }

        return $this->initializedReferences;
    }

    /**
     * @param callable $callback
     * @return array
     */
    public function filterFixtures(callable $callback)
    {
        $values = [];
        foreach ($this->getReferences() as $index => $reference) {
            if ($callback($reference, $index)) {
                $values[$reference->getId()] = $reference;
            }
        }

        return array_values($values);
    }

    protected function get(string $url)
    {
        $this->client->request(
            'GET',
            $url,
            [],
            [],
            ['HTTP_Authorization' => 'token']
        );
    }

    protected function post(string $url, array $content, bool $skipAuth = false)
    {
        $this->client->request(
            'POST',
            $url,
            [],
            [],
            ['HTTP_Authorization' => $skipAuth ? null : 'token'],
            json_encode($content)
        );
    }

    protected function delete(string $url)
    {
        $this->client->request(
            'DELETE',
            $url,
            [],
            [],
            ['HTTP_Authorization' => 'token']
        );
    }

    protected function setCurrentUser(User $user)
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);
    }

    protected function getContent()
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    protected function assertResponseStatusCode(int $code)
    {
        $this->assertStatusCode($code, $this->client);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->fixtures && $this->fixtures->getManager()) {
            $this->fixtures->getManager()->clear();
            $this->fixtures = null;
        }

        $this->fixturesTearDown();

        $this->initializedReferences = null;
    }
}
