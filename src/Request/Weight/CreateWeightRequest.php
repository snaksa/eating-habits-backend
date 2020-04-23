<?php

namespace App\Request\Weight;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateWeightRequest extends BaseRequest
{
    /**
     * @Assert\NotBlank(message="Date should not be blank")
     */
    public string $date;

    /**
     * @Assert\Positive(message="Weight should be a positive number")
     * @Assert\NotBlank(message="Weight should not be blank")
     */
    public int $weight;
}
