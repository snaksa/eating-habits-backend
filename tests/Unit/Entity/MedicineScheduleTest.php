<?php

namespace App\Tests\Unit\Entity;

use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class MedicineScheduleTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setId(1)
            ->setUsername('test@gmail.com')
            ->setName('John Doe')
            ->setPassword('123456')
            ->setRoles(['ROLE_USER']);

        $date = $this->getCurrentDateTime();

        $medicine = (new Medicine())
            ->setId(1)
            ->setName('Medicine 1')
            ->setFrequency(MedicineFrequencies::EVERYDAY)
            ->setImage('image')
            ->setUser($user);

        $medicineSchedule = (new MedicineSchedule())
            ->setId(1)
            ->setIntakeTime($date)
            ->setPeriodSpan(100)
            ->setMedicine($medicine);

        $this->assertEquals(1, $medicineSchedule->getId());
        $this->assertEquals($date, $medicineSchedule->getIntakeTime());
        $this->assertEquals(100, $medicineSchedule->getPeriodSpan());
        $this->assertEquals($medicine, $medicineSchedule->getMedicine());

        $intake = (new MedicineIntake())->setDate($date);
        $medicineSchedule->addIntake($intake);
        $this->assertEquals(1, $medicineSchedule->getIntakes()->count());
        $medicineSchedule->removeIntake($intake);
        $this->assertEquals(0, $medicineSchedule->getIntakes()->count());
    }
}
