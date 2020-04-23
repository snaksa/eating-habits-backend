<?php

namespace App\Request\Meal;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMealRequest extends BaseRequest
{
    public ?string $description = null;
    public ?string $picture = null;

    /**
     * @Assert\NotBlank(message="Date should not be blank")
     */
    public string $date;

    /**
     * @Assert\NotBlank(message="Type should not be blank")
     */
    public int $type;
}
