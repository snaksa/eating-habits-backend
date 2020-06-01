<?php

namespace App\Tests\Unit\Transformers;

use App\Entity\Medicine;
use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use App\Traits\DateUtils;
use App\Transformers\MedicineScheduleTransformer;
use PHPUnit\Framework\TestCase;

class MedicineScheduleTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $date = $this->getCurrentDateTime();

        $medicineSchedule = (new MedicineSchedule())
            ->setId(1)
            ->setPeriodSpan(100)
            ->setIntakeTime($date);

        $expected = [
            'id' => $medicineSchedule->getId(),
            'intakeTime' => $this->formatDate($medicineSchedule->getIntakeTime()),
            'periodSpan' => $medicineSchedule->getPeriodSpan()
        ];

        $transformer = new MedicineScheduleTransformer();
        $this->assertEquals($expected, $transformer->transform($medicineSchedule));
    }

    public function testIncludeIntakes()
    {
        $date = $this->getCurrentDateTime();
        $intakes = [
            (new MedicineIntake())->setId(1),
            (new MedicineIntake())->setId(2)
        ];

        $medicineSchedule = (new MedicineSchedule())
            ->setId(1)
            ->setPeriodSpan(100)
            ->setIntakeTime($date)
            ->addIntake($intakes[0])
            ->addIntake($intakes[1]);

        $transformer = new MedicineScheduleTransformer();
        $this->assertEquals($medicineSchedule->getIntakes(), $transformer->includeIntakes($medicineSchedule)->getData());
    }

    public function testIncludeMedicine()
    {
        $medicine = (new Medicine())->setId(1);
        $medicineSchedule = new MedicineSchedule();
        $medicineSchedule->setMedicine($medicine);

        $transformer = new MedicineScheduleTransformer();
        $this->assertEquals($medicine, $transformer->includeMedicine($medicineSchedule)->getData());
    }
}
