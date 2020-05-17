<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class ApiKeyTransformer extends TransformerAbstract
{
    public function transform(array $auth)
    {
        return [
            'token' => $auth['token'],
            'expiresIn' => $auth['expiresIn'],
        ];
    }
}
