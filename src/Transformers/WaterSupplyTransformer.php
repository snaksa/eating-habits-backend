<?php

namespace App\Transformers;

use App\Entity\WaterSupply;
use App\Traits\DateUtils;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class WaterSupplyTransformer extends TransformerAbstract
{
    use DateUtils;

    protected $availableIncludes = [
        'user'
    ];

    public function transform(WaterSupply $waterSupply)
    {
        return [
            'id' => $waterSupply->getId(),
            'date' => $this->formatDate($waterSupply->getDate()),
            'amount' => $waterSupply->getAmount()
        ];
    }

    public function includeUser(WaterSupply $waterSupply): Item
    {
        return $this->item($waterSupply->getUser(), new UserTransformer());
    }
}
