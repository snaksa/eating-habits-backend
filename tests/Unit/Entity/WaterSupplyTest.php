<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\WaterSupply;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class WaterSupplyTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setUsername('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $date = $this->getCurrentDateTime();

        $waterSupply = (new WaterSupply())
            ->setId(1)
            ->setAmount(250)
            ->setDate($date)
            ->setUser($user);

        $this->assertEquals(1, $waterSupply->getId());
        $this->assertEquals(250, $waterSupply->getAmount());
        $this->assertEquals($date, $waterSupply->getDate());
        $this->assertEquals($user, $waterSupply->getUser());
    }
}
