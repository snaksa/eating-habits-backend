<?php

namespace App\Tests\Unit\Constant;

use App\Constant\Gender;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    public function testAllTypesMethod()
    {
        $expected = [
            'Male' => 0,
            'Female' => 1,
        ];

        $this->assertEquals($expected, Gender::all());
    }
}
