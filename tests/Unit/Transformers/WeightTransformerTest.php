<?php

namespace App\Tests\Unit\Transformers;

use App\Entity\User;
use App\Entity\Weight;
use App\Traits\DateUtils;
use App\Transformers\WeightTransformer;
use PHPUnit\Framework\TestCase;

class WeightTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $date = $this->getCurrentDateTime();
        $weight = new Weight();
        $weight->setId(1)
            ->setWeight(80)
            ->setDate($date);

        $expected = [
            'id' => 1,
            'weight' => 80,
            'date' => $this->formatDate($date),
        ];

        $transformer = new WeightTransformer();
        $this->assertEquals($expected, $transformer->transform($weight));
    }

    public function testIncludeUser()
    {
        $user = (new User())->setId(1)->setName('John Doe')->setUsername('test@gmail.com');
        $weight = new Weight();
        $weight->setUser($user);

        $transformer = new WeightTransformer();
        $this->assertEquals($user, $transformer->includeUser($weight)->getData());
    }
}
