<?php

namespace App\Request\Meal;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateMealRequest extends BaseRequest
{
    public ?string $description = null;
    public ?string $date = null;
    public ?int $type = null;
    public ?string $picture = null;
}
