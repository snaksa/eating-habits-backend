<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Meal;
use App\Entity\User;
use App\Entity\WaterSupply;
use App\Entity\Weight;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setId(1)
            ->setUsername('test@gmail.com')
            ->setName('John Doe')
            ->setPassword('123456')
            ->setRoles(['ROLE_USER']);

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('test@gmail.com', $user->getUsername());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('123456', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertEquals(null, $user->getSalt());
        $this->assertEquals(null, $user->eraseCredentials());

        $weight = (new Weight())->setWeight(80);
        $user->addWeight($weight);
        $this->assertEquals(1, $user->getWeights()->count());
        $user->removeWeight($weight);
        $this->assertEquals(0, $user->getWeights()->count());

        $waterSupply = (new WaterSupply())->setAmount(80);
        $user->addWaterSupply($waterSupply);
        $this->assertEquals(1, $user->getWaterSupplies()->count());
        $user->removeWaterSupply($waterSupply);
        $this->assertEquals(0, $user->getWaterSupplies()->count());

        $meal = (new Meal())->setDescription('test');
        $user->addMeal($meal);
        $this->assertEquals(1, $user->getMeals()->count());
        $user->removeMeal($meal);
        $this->assertEquals(0, $user->getMeals()->count());
    }
}
