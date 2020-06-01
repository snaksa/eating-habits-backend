<?php

namespace App\Tests\Unit\Transformers;

use App\Entity\User;
use App\Entity\WaterSupply;
use App\Traits\DateUtils;
use App\Transformers\WaterSupplyTransformer;
use PHPUnit\Framework\TestCase;

class WaterSupplyTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $date = $this->getCurrentDateTime();
        $waterSupply = new WaterSupply();
        $waterSupply->setId(1)
            ->setAmount(250)
            ->setDate($date);

        $expected = [
            'id' => 1,
            'amount' => 250,
            'date' => $this->formatDate($date),
        ];

        $transformer = new WaterSupplyTransformer();
        $this->assertEquals($expected, $transformer->transform($waterSupply));
    }

    public function testIncludeUser()
    {
        $user = (new User())->setId(1)->setName('John Doe')->setUsername('test@gmail.com');
        $waterSupply = new WaterSupply();
        $waterSupply->setUser($user);

        $transformer = new WaterSupplyTransformer();
        $this->assertEquals($user, $transformer->includeUser($waterSupply)->getData());
    }
}
