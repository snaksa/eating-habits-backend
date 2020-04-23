<?php

namespace App\Transformers;

use App\Entity\WaterSupply;
use League\Fractal\TransformerAbstract;

class WaterSupplyTransformer extends TransformerAbstract
{
    public function transform(WaterSupply $waterSupply)
    {
        return [
            'id' => $waterSupply->getId()
        ];
    }
}
