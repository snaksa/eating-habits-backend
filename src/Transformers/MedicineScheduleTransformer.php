<?php

namespace App\Transformers;

use App\Entity\MedicineSchedule;
use App\Traits\DateUtils;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class MedicineScheduleTransformer extends TransformerAbstract
{
    use DateUtils;

    protected $availableIncludes = [
        'medicine',
        'intakes'
    ];

    public function transform(MedicineSchedule $medicineSchedule)
    {
        return [
            'id' => $medicineSchedule->getId(),
            'intakeTime' => $medicineSchedule->getIntakeTime()
                ? $this->formatDate($medicineSchedule->getIntakeTime())
                : null,
            'periodSpan' => $medicineSchedule->getPeriodSpan()
        ];
    }

    public function includeMedicine(MedicineSchedule $schedule): Item
    {
        return $this->item($schedule->getMedicine(), new MedicineTransformer());
    }

    public function includeIntakes(MedicineSchedule $schedule): Collection
    {
        return $this->collection($schedule->getIntakes(), new MedicineIntakeTransformer());
    }
}
