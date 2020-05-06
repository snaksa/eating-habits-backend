<?php

namespace App\Transformers;

use App\Traits\DateUtils;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use App\Entity\Weight;

class WeightTransformer extends TransformerAbstract
{
    use DateUtils;

    protected $availableIncludes = [
        'user'
    ];

    public function transform(Weight $weight)
    {
        return [
            'id' => $weight->getId(),
            'date' => $this->formatDate($weight->getDate()),
            'weight' => $weight->getWeight(),
        ];
    }

    public function includeUser(Weight $weight): Item
    {
        return $this->item($weight->getUser(), new UserTransformer());
    }
}
