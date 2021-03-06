<?php

namespace App\Transformers;

use App\Entity\MedicineIntake;
use App\Traits\DateUtils;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class MedicineIntakeTransformer extends TransformerAbstract
{
    use DateUtils;

    protected $availableIncludes = [
        'medicineSchedule',
    ];

    public function transform(MedicineIntake $medicineIntake)
    {
        return [
            'id' => $medicineIntake->getId(),
            'date' => $this->formatDate($medicineIntake->getDate()),
        ];
    }

    public function includeMedicineSchedule(MedicineIntake $intake): Item
    {
        return $this->item($intake->getMedicineSchedule(), new MedicineScheduleTransformer());
    }
}
