<?php

namespace App\Tests\Unit\Transformers;

use App\Entity\Meal;
use App\Entity\User;
use App\Entity\WaterSupply;
use App\Entity\Weight;
use App\Traits\DateUtils;
use App\Transformers\UserTransformer;
use PHPUnit\Framework\TestCase;

class UserTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $user = (new User())
            ->setId(1)
            ->setUsername('test@gmail.com')
            ->setName('John Doe');

        $expected = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'name' => $user->getName(),
            'lang' => $user->getLang(),
            'age' => $user->getAge(),
            'gender' => $user->getGender(),
            'height' => $user->getHeight(),
            'water_calculation' => $user->getWaterCalculation(),
            'water_amount' => $user->getWaterAmount(),
        ];

        $transformer = new UserTransformer();
        $this->assertEquals($expected, $transformer->transform($user));
    }

    public function testIncludeMeals()
    {
        $meals = [
            (new Meal())->setId(1),
            (new Meal())->setId(2)
        ];
        $user = (new User())
            ->setId(1)
            ->setUsername('test@gmail.com')
            ->setName('John Doe')
            ->addMeal($meals[0])
            ->addMeal($meals[1]);

        $transformer = new UserTransformer();
        $this->assertEquals($user->getMeals(), $transformer->includeMeals($user)->getData());
    }

    public function testIncludeWeights()
    {
        $weights = [
            (new Weight())->setId(1),
            (new Weight())->setId(2)
        ];
        $user = (new User())
            ->setId(1)
            ->setUsername('test@gmail.com')
            ->setName('John Doe')
            ->addWeight($weights[0])
            ->addWeight($weights[1]);

        $transformer = new UserTransformer();
        $this->assertEquals($user->getWeights(), $transformer->includeWeights($user)->getData());
    }

    public function testIncludeWaterSupplies()
    {
        $waterSupplies = [
            (new WaterSupply())->setId(1),
            (new WaterSupply())->setId(2)
        ];
        $user = (new User())
            ->setId(1)
            ->setUsername('test@gmail.com')
            ->setName('John Doe')
            ->addWaterSupply($waterSupplies[0])
            ->addWaterSupply($waterSupplies[1]);

        $transformer = new UserTransformer();
        $this->assertEquals($user->getWaterSupplies(), $transformer->includeWaterSupplies($user)->getData());
    }
}
