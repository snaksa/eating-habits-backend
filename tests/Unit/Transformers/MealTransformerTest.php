<?php

namespace App\Tests\Unit\Transformers;

use App\Constant\MealTypes;
use App\Entity\Meal;
use App\Entity\User;
use App\Traits\DateUtils;
use App\Transformers\MealTransformer;
use PHPUnit\Framework\TestCase;

class MealTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $date = $this->getCurrentDateTime();
        $meal = new Meal();
        $meal->setId(1)
            ->setType(MealTypes::LUNCH)
            ->setDescription('Description')
            ->setDate($date);

        $expected = [
            'id' => 1,
            'type' => MealTypes::LUNCH,
            'description' => 'Description',
            'date' => $this->formatDate($date),
        ];

        $transformer = new MealTransformer();
        $this->assertEquals($expected, $transformer->transform($meal));
    }

    public function testIncludeUser()
    {
        $user = (new User())->setId(1)->setName('John Doe')->setUsername('test@gmail.com');
        $weight = new Meal();
        $weight->setUser($user);

        $transformer = new MealTransformer();
        $this->assertEquals($user, $transformer->includeUser($weight)->getData());
    }
}
