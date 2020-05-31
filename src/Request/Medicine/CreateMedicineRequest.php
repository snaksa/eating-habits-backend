<?php

namespace App\Request\Medicine;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMedicineRequest extends BaseRequest
{
    /**
     * @Assert\NotBlank(message="name should not be blank")
     */
    public string $name;

    public ?string $image = null;
    public ?int $frequency = null;
}
