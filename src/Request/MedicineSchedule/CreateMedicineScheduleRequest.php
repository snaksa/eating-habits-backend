<?php

namespace App\Request\MedicineSchedule;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMedicineScheduleRequest extends BaseRequest
{
    public ?int $periodSpan = null;

    /**
     * @Assert\NotBlank(message="medicineId should not be blank")
     */
    public int $medicineId;

    /**
     * @Assert\NotBlank(message="intakeTime should not be blank")
     */
    public string $intakeTime;
}
