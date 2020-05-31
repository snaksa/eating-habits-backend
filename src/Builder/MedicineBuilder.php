<?php

namespace App\Builder;

use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\User;
use App\Exception\InvalidDateException;
use App\Exception\InvalidMedicineFrequencyException;
use App\Request\Medicine\CreateMedicineRequest;
use App\Request\Medicine\UpdateMedicineRequest;
use App\Traits\DateUtils;

class MedicineBuilder extends BaseBuilder
{
    use DateUtils;

    private Medicine $medicine;

    public function create(): self
    {
        $this->medicine = new Medicine();

        return $this;
    }

    /**
     * @param CreateMedicineRequest|UpdateMedicineRequest $input
     * @return $this
     * @throws InvalidDateException
     * @throws InvalidMedicineFrequencyException
     */
    public function bind($input): self
    {
        if ($input->name !== null) {
            $this->setName($input->name);
        }

        if ($input->image !== null) {
            // TODO: upload picture
            $this->setImage($input->image);
        }

        if ($input->frequency !== null) {
            if (!in_array($input->frequency, MedicineFrequencies::all())) {
                throw new InvalidMedicineFrequencyException("Medicine Frequency {$input->frequency} does not exist");
            }

            $this->setFrequency($input->frequency);
        }

        return $this;
    }

    public function setMedicine(Medicine $medicine): self
    {
        $this->medicine = $medicine;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->medicine->setName($name);

        return $this;
    }

    public function setFrequency(int $frequency): self
    {
        $this->medicine->setFrequency($frequency);

        return $this;
    }

    public function setImage(string $image)
    {
        $this->medicine->setImage($image);
    }

    public function setUser(User $user): self
    {
        $this->medicine->setUser($user);

        return $this;
    }

    public function build(): Medicine
    {
        return $this->medicine;
    }
}
