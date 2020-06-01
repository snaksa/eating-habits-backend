<?php

namespace App\Request\MedicineIntake;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMedicineIntakeRequest extends BaseRequest
{
    /**
     * @Assert\NotBlank(message="medicineScheduleId should not be blank")
     */
    public int $medicineScheduleId;

    /**
     * @Assert\NotBlank(message="date should not be blank")
     */
    public string $date;
}
