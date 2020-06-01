<?php

namespace App\Tests\Unit\Entity;

use App\Constant\MedicineFrequencies;
use App\Entity\Meal;
use App\Entity\Medicine;
use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use App\Entity\WaterSupply;
use App\Entity\Weight;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class MedicineIntakeTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $date = $this->getCurrentDateTime();
        $medicineSchedule = (new MedicineSchedule())->setIntakeTime($date);

        $medicineIntake = (new MedicineIntake())
            ->setId(1)
            ->setDate($date)
        ->setMedicineSchedule($medicineSchedule);

        $this->assertEquals(1, $medicineIntake->getId());
        $this->assertEquals($date, $medicineIntake->getDate());
        $this->assertEquals($medicineSchedule, $medicineIntake->getMedicineSchedule());
    }
}
