<?php

namespace App\Tests\Unit\Entity;

use App\Constant\MealTypes;
use App\Entity\Meal;
use App\Entity\User;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class MealTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setUsername('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $date = $this->getCurrentDateTime();

        $meal = (new Meal())
            ->setId(1)
            ->setType(MealTypes::LUNCH)
            ->setDescription('Two meatballs with one cucumber')
            ->setPicture('/path/to/image.png')
            ->setDate($date)
            ->setUser($user);

        $this->assertEquals(1, $meal->getId());
        $this->assertEquals(MealTypes::LUNCH, $meal->getType());
        $this->assertEquals('Two meatballs with one cucumber', $meal->getDescription());
        $this->assertEquals('/path/to/image.png', $meal->getPicture());
        $this->assertEquals($date, $meal->getDate());
        $this->assertEquals($user, $meal->getUser());
    }
}
