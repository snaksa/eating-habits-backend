<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entity\Weight;

class WeightTransformer extends TransformerAbstract
{
    public function transform(Weight $weight)
    {
        return [
            'id' => $weight->getId()
        ];
    }
}
