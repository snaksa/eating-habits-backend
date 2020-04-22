<?php

namespace App\Request\Meal;

use Symfony\Component\Validator\Constraints as Assert;
use App\Request\BaseRequest;

class CreateMealRequest extends BaseRequest
{
    /**
     * @var string
     * @ Assert\NotBlank(message="Description should not be blank")
     */
    public ?string $description = null;
}
