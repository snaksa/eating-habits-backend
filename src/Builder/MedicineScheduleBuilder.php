<?php

namespace App\Builder;

use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\MedicineSchedule;
use App\Exception\InvalidDataException;
use App\Exception\InvalidDateException;
use App\Repository\MedicineRepository;
use App\Request\MedicineSchedule\CreateMedicineScheduleRequest;
use App\Request\MedicineSchedule\UpdateMedicineScheduleRequest;
use App\Traits\DateUtils;

class MedicineScheduleBuilder extends BaseBuilder
{
    use DateUtils;

    private MedicineSchedule $medicineSchedule;
    private MedicineRepository $medicineRepository;

    public function __construct(MedicineRepository $medicineRepository)
    {
        $this->medicineRepository = $medicineRepository;
    }

    public function create(): self
    {
        $this->medicineSchedule = new MedicineSchedule();

        return $this;
    }

    /**
     * @param CreateMedicineScheduleRequest|UpdateMedicineScheduleRequest $input
     * @return $this
     * @throws InvalidDateException
     * @throws InvalidDataException
     */
    public function bind($input): self
    {
        if ($input->intakeTime !== null) {
            $date = $this->createFromFormat($input->intakeTime, $this->dateTimeFormat);

            if (!$date) {
                throw new InvalidDateException('Intake time is not valid');
            }

            $this->setIntakeTime($date);
        }

        if ($input->periodSpan !== null) {
            $this->setPeriodSpan($input->periodSpan);
        }

        if ($input->medicineId !== null) {
            $medicine = $this->findEntity($input->medicineId, $this->medicineRepository);
            if (!$medicine) {
                throw new InvalidDateException("Medicine with ID {$input->medicineId} does not exist");
            }

            $this->setMedicine($medicine);
        }

        if ($this->medicineSchedule->getMedicine()->getFrequency() === MedicineFrequencies::PERIOD
            && !$this->medicineSchedule->getPeriodSpan()) {
            throw new InvalidDataException('Medicine frequency is periodic and periodSpan should be specified');
        }
        return $this;
    }

    public function setMedicineSchedule(MedicineSchedule $medicineSchedule): self
    {
        $this->medicineSchedule = $medicineSchedule;

        return $this;
    }

    public function setMedicine(Medicine $medicine): self
    {
        $this->medicineSchedule->setMedicine($medicine);

        return $this;
    }

    public function setIntakeTime(\DateTime $intakeTime)
    {
        $this->medicineSchedule->setIntakeTime($intakeTime);
    }

    public function setPeriodSpan(int $periodSpan): self
    {
        $this->medicineSchedule->setPeriodSpan($periodSpan);

        return $this;
    }

    public function build(): MedicineSchedule
    {
        return $this->medicineSchedule;
    }
}
