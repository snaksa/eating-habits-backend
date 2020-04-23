<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class ApiKeyTransformer extends TransformerAbstract
{
    public function transform(string $key)
    {
        return [
            'apiKey' => $key
        ];
    }
}
