<?php

namespace App\Transformers;

use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use App\Entity\User;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'meals',
        'waterSupply',
        'weight'
    ];

    public function transform(User $user)
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'name' => $user->getName() ?? $user->getUsername(),
        ];
    }

    public function includeMeals(User $user): Collection
    {
        return $this->collection($user->getMeals(), new MealTransformer());
    }

    public function includeWaterSupply(User $user): Collection
    {
        return $this->collection($user->getWaterSupplies(), new WaterSupplyTransformer());
    }

    public function includeWeight(User $user): Collection
    {
        return $this->collection($user->getWeights(), new WeightTransformer());
    }
}
