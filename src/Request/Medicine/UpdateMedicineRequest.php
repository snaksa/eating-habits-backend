<?php

namespace App\Request\Medicine;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateMedicineRequest extends BaseRequest
{
    public ?string $name = null;
    public ?string $image = null;
    public ?int $frequency = null;
}
