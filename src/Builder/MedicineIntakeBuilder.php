<?php

namespace App\Builder;

use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use App\Exception\InvalidDataException;
use App\Exception\InvalidDateException;
use App\Repository\MedicineIntakeRepository;
use App\Repository\MedicineScheduleRepository;
use App\Request\MedicineIntake\CreateMedicineIntakeRequest;
use App\Traits\DateUtils;

class MedicineIntakeBuilder extends BaseBuilder
{
    use DateUtils;

    private MedicineIntake $medicineIntake;
    private MedicineIntakeRepository $medicineIntakeRepository;
    private MedicineScheduleRepository $medicineScheduleRepository;

    public function __construct(
        MedicineIntakeRepository $medicineIntakeRepository,
        MedicineScheduleRepository $medicineScheduleRepository
    ) {
        $this->medicineIntakeRepository = $medicineIntakeRepository;
        $this->medicineScheduleRepository = $medicineScheduleRepository;
    }

    public function create(): self
    {
        $this->medicineIntake = new MedicineIntake();

        return $this;
    }

    /**
     * @param CreateMedicineIntakeRequest $input
     * @return $this
     * @throws InvalidDateException
     * @throws InvalidDataException
     */
    public function bind($input): self
    {
        if ($input->date !== null) {
            $date = $this->createFromFormat($input->date, $this->dateTimeFormat);

            if (!$date) {
                throw new InvalidDateException('Date is not valid');
            }

            $this->setDate($date);
        }

        if ($input->medicineScheduleId !== null) {
            $medicine = $this->findEntity($input->medicineScheduleId, $this->medicineScheduleRepository);
            if (!$medicine) {
                throw new InvalidDataException(
                    "MedicineSchedule with ID {$input->medicineScheduleId} does not exist"
                );
            }

            $this->setMedicineSchedule($medicine);
        }

        $endDate = (clone $this->medicineIntake->getDate())->modify('+ 24 hours');
        $existingRecord = $this->medicineIntakeRepository->findExistingIntake(
            $this->medicineIntake->getMedicineSchedule(),
            $this->medicineIntake->getDate(),
            $endDate
        );

        if ($existingRecord) {
            throw new InvalidDataException("Intake record already exists");
        }

        return $this;
    }

    public function setMedicineIntake(MedicineIntake $medicineIntake): self
    {
        $this->medicineIntake = $medicineIntake;

        return $this;
    }

    public function setMedicineSchedule(MedicineSchedule $medicineSchedule): self
    {
        $this->medicineIntake->setMedicineSchedule($medicineSchedule);

        return $this;
    }

    public function setDate(\DateTime $date)
    {
        $this->medicineIntake->setDate($date);
    }

    public function build(): MedicineIntake
    {
        return $this->medicineIntake;
    }
}
