<?php

namespace App\Request\Weight;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateWeightRequest extends BaseRequest
{
    public ?string $date = null;

    /**
     * @Assert\Positive(message="Weight should be a positive number")
     */
    public ?float $weight = null;
}
