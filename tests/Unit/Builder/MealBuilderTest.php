<?php

namespace App\Tests\Unit\Builder;

use App\Builder\MealBuilder;
use App\Constant\MealTypes;
use App\Entity\Meal;
use App\Entity\User;
use App\Exception\InvalidDateException;
use App\Exception\InvalidMealTypeException;
use App\Request\Meal\CreateMealRequest;
use App\Request\Meal\UpdateMealRequest;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MealBuilderTest extends TestCase
{
    use DateUtils;

    public function test_meal_builder_create()
    {
        $user = (new User())->setId(1);

        $service = new MealBuilder();

        $meal = $service
            ->create()
            ->setUser($user)
            ->build();

        $this->assertEquals($user, $meal->getUser());
    }

    public function test_meal_builder_bind_create_request()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateMealRequest(new Request());
        $request->description = 'test';
        $request->picture = 'picture';
        $request->type = MealTypes::LUNCH;
        $request->date = $date->format('Y-m-d H:i:s');

        $service = new MealBuilder();

        $meal = $service
            ->create()
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $meal->getUser());
        $this->assertEquals('test', $meal->getDescription());
        $this->assertEquals('picture', $meal->getPicture());
        $this->assertEquals(MealTypes::LUNCH, $meal->getType());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $meal->getDate()->format('Y-m-d H:i:s'));
    }

    public function test_meal_builder_bind_create_request_invalid_date_exception()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateMealRequest(new Request());
        $request->description = 'test';
        $request->picture = 'picture';
        $request->type = MealTypes::LUNCH;
        $request->date = $date->format('Y-m-d H:i');

        $service = new MealBuilder();

        $this->expectException(InvalidDateException::class);

        $service->create()
            ->setUser($user)
            ->bind($request)
            ->build();
    }

    public function test_meal_builder_bind_create_request_invalid_meal_type()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateMealRequest(new Request());
        $request->description = 'test';
        $request->picture = 'picture';
        $request->type = -1;
        $request->date = $date->format('Y-m-d H:i');

        $service = new MealBuilder();

        $this->expectException(InvalidMealTypeException::class);

        $service->create()
            ->setUser($user)
            ->bind($request)
            ->build();
    }

    public function test_meal_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();
        $meal = (new Meal())
            ->setId(1)
            ->setDescription('test1')
            ->setPicture('picture1')
            ->setType(MealTypes::SNACK)
            ->setDate($this->getCurrentDateTime()->modify('- 2 days'));

        $request = new UpdateMealRequest(new Request());
        $request->description = 'test2';
        $request->picture = 'picture2';
        $request->type = MealTypes::LUNCH;
        $request->date = $date->format('Y-m-d H:i:s');

        $service = new MealBuilder();

        $meal = $service
            ->setMeal($meal)
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $meal->getUser());
        $this->assertEquals('test2', $meal->getDescription());
        $this->assertEquals('picture2', $meal->getPicture());
        $this->assertEquals(MealTypes::LUNCH, $meal->getType());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $meal->getDate()->format('Y-m-d H:i:s'));
    }
}
