<?php

namespace App\Transformers;

use App\Entity\Medicine;
use App\Traits\DateUtils;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class MedicineTransformer extends TransformerAbstract
{
    use DateUtils;

    protected $availableIncludes = [
        'user',
        'schedule'
    ];

    public function transform(Medicine $medicine)
    {
        return [
            'id' => $medicine->getId(),
            'name' => $medicine->getName(),
            'frequency' => $medicine->getFrequency(),
        ];
    }

    public function includeUser(Medicine $medicine): Item
    {
        return $this->item($medicine->getUser(), new UserTransformer());
    }

    public function includeSchedule(Medicine $medicine): Collection
    {
        return $this->collection($medicine->getSchedule(), new MedicineScheduleTransformer());
    }
}
