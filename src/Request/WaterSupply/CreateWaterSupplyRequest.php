<?php

namespace App\Request\WaterSupply;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateWaterSupplyRequest extends BaseRequest
{
    /**
     * @Assert\NotBlank(message="Date should not be blank")
     */
    public string $date;

    /**
     * @Assert\Positive(message="Amount should be a positive number")
     * @Assert\NotBlank(message="Amount should not be blank")
     */
    public int $amount;
}
