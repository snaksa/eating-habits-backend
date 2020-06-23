<?php

namespace App\Request\User;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest extends BaseRequest
{
    public ?string $name = null;
    public ?string $lang = null;
    public ?bool $water_calculation = null;
    public ?int $gender = null;

    /**
     * @Assert\Positive(message="age should be a positive number")
     */
    public ?int $age = null;

    /**
     * @Assert\Positive(message="height should be a positive number")
     */
    public ?int $height = null;

    /**
     * @Assert\Positive(message="water_amount should be a positive number")
     */
    public ?int $water_amount = null;

}
