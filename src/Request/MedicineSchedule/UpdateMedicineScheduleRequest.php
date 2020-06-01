<?php

namespace App\Request\MedicineSchedule;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateMedicineScheduleRequest extends BaseRequest
{
    public ?int $periodSpan = null;
    public ?string $intakeTime = null;
}
