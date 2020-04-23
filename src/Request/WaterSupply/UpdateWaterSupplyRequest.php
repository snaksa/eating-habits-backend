<?php

namespace App\Request\WaterSupply;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateWaterSupplyRequest extends BaseRequest
{
    public ?string $date = null;

    /**
     * @Assert\Positive(message="Amount should be a positive number")
     */
    public ?int $amount = null;
}
