<?php

namespace App\Tests\Unit\Constant;

use App\Constant\MedicineFrequencies;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class MedicineFrequenciesTest extends TestCase
{
    use DateUtils;

    public function testAllTypesMethod()
    {
        $expected = [
            'Everyday' => 1,
            'Period' => 2,
            'Once' => 3
        ];

        $this->assertEquals($expected, MedicineFrequencies::all());
    }
}
