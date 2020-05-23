<?php

namespace App\Builder;

use App\Entity\User;
use App\Entity\WaterSupply;
use App\Exception\InvalidDateException;
use App\Request\WaterSupply\CreateWaterSupplyRequest;
use App\Request\WaterSupply\UpdateWaterSupplyRequest;
use App\Traits\DateUtils;

class WaterSupplyBuilder extends BaseBuilder
{
    use DateUtils;

    private WaterSupply $waterSupply;

    public function create(): self
    {
        $this->waterSupply = new WaterSupply();

        return $this;
    }

    /**
     * @param CreateWaterSupplyRequest|UpdateWaterSupplyRequest $input
     * @return $this
     * @throws InvalidDateException
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

        if ($input->amount !== null) {
            $this->setAmount($input->amount);
        }

        return $this;
    }

    public function setWaterSupply(WaterSupply $waterSupply): self
    {
        $this->waterSupply = $waterSupply;

        return $this;
    }

    public function setDate(\DateTimeInterface $date)
    {
        $this->waterSupply->setDate($date);
    }

    public function setAmount(int $amount): self
    {
        $this->waterSupply->setAmount($amount);

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->waterSupply->setUser($user);

        return $this;
    }

    public function build(): WaterSupply
    {
        return $this->waterSupply;
    }
}
