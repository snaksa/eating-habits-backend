<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Weight;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setUsername('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $date = $this->getCurrentDateTime();

        $weight = (new Weight())
            ->setId(1)
            ->setWeight(80)
            ->setDate($date)
            ->setUser($user);

        $this->assertEquals(1, $weight->getId());
        $this->assertEquals(80, $weight->getWeight());
        $this->assertEquals($date, $weight->getDate());
        $this->assertEquals($user, $weight->getUser());
    }
}
