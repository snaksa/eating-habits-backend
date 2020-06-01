<?php

namespace App\Tests\Unit\Transformers;

use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use App\Traits\DateUtils;
use App\Transformers\MedicineTransformer;
use PHPUnit\Framework\TestCase;

class MedicineTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $medicine = (new Medicine())
            ->setId(1)
            ->setName('name')
            ->setImage('image')
            ->setFrequency(MedicineFrequencies::PERIOD);

        $expected = [
            'id' => $medicine->getId(),
            'name' => $medicine->getName(),
            'frequency' => $medicine->getFrequency(),
        ];

        $transformer = new MedicineTransformer();
        $this->assertEquals($expected, $transformer->transform($medicine));
    }

    public function testIncludeSchedule()
    {
        $schedules = [
            (new MedicineSchedule())->setId(1),
            (new MedicineSchedule())->setId(2)
        ];

        $medicine = (new Medicine())
            ->setId(1)
            ->setName('name')
            ->setImage('image')
            ->setFrequency(MedicineFrequencies::PERIOD)
            ->addSchedule($schedules[0])
            ->addSchedule($schedules[1]);

        $transformer = new MedicineTransformer();
        $this->assertEquals($medicine->getSchedule(), $transformer->includeSchedule($medicine)->getData());
    }

    public function testIncludeUser()
    {
        $user = (new User())->setId(1)->setName('John Doe')->setUsername('test@gmail.com');
        $medicine = new Medicine();
        $medicine->setUser($user);

        $transformer = new MedicineTransformer();
        $this->assertEquals($user, $transformer->includeUser($medicine)->getData());
    }
}
