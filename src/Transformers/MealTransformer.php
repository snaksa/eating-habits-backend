<?php

namespace App\Transformers;

use App\Traits\DateUtils;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use App\Entity\Meal;

class MealTransformer extends TransformerAbstract
{
    use DateUtils;

    protected $availableIncludes = [
        'user'
    ];

    public function transform(Meal $meal)
    {
        return [
            'id' => $meal->getId(),
            'description' => $meal->getDescription(),
            'date' => $this->formatDate($meal->getDate()),
            'type' => $meal->getType()
        ];
    }

    public function includeUser(Meal $meal): Item
    {
        return $this->item($meal->getUser(), new UserTransformer());
    }
}
