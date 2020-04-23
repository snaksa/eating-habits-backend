<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entity\User;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->getId()
        ];
    }
}
