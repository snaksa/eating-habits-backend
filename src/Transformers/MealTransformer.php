<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entity\Meal;

class MealTransformer extends TransformerAbstract
{
    public function transform(Meal $meal)
    {
        return [
            'id' => $meal->getId()
        ];
    }
}
