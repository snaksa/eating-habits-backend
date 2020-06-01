<?php

namespace App\Tests\Unit\Transformers;

use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use App\Traits\DateUtils;
use App\Transformers\MedicineIntakeTransformer;
use PHPUnit\Framework\TestCase;

class MedicineIntakeTransformerTest extends TestCase
{
    use DateUtils;

    public function testTransform()
    {
        $date = $this->getCurrentDateTime();
        $schedule = (new MedicineSchedule())->setId(1);

        $intake = (new MedicineIntake())
            ->setId(1)
            ->setMedicineSchedule($schedule)
            ->setDate($date);

        $expected = [
            'id' => $intake->getId(),
            'date' => $this->formatDate($date),
        ];

        $transformer = new MedicineIntakeTransformer();
        $this->assertEquals($expected, $transformer->transform($intake));
    }

    public function testIncludeMedicineSchedule()
    {
        $medicineSchedule = (new MedicineSchedule())->setId(1);
        $medicineIntake = new MedicineIntake();
        $medicineIntake->setMedicineSchedule($medicineSchedule);

        $transformer = new MedicineIntakeTransformer();
        $this->assertEquals($medicineSchedule, $transformer->includeMedicineSchedule($medicineIntake)->getData());
    }
}
