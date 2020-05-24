<?php

namespace App\Tests\Unit\Constant;

use App\Constant\MealTypes;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class MealTypesTest extends TestCase
{
    use DateUtils;

    public function testAllTypesMethod()
    {
        $expected = [
            'Breakfast' => 1,
            'Lunch' => 2,
            'Dinner' => 3,
            'Snack' => 4
        ];

        $this->assertEquals($expected, MealTypes::all());
    }
}
