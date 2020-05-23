<?php

namespace App\Builder;

use App\Entity\User;
use App\Entity\Weight;
use App\Exception\InvalidDateException;
use App\Request\Weight\CreateWeightRequest;
use App\Request\Weight\UpdateWeightRequest;
use App\Traits\DateUtils;

class WeightBuilder extends BaseBuilder
{
    use DateUtils;

    private Weight $weight;

    public function create(): self
    {
        $this->weight = new Weight();

        return $this;
    }

    /**
     * @param CreateWeightRequest|UpdateWeightRequest $input
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

        if ($input->weight !== null) {
            $this->setWeightAmount($input->weight);
        }

        return $this;
    }

    public function setWeight(Weight $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function setDate(\DateTimeInterface $date)
    {
        $this->weight->setDate($date);
    }

    public function setWeightAmount(string $weight): self
    {
        $this->weight->setWeight($weight);

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->weight->setUser($user);

        return $this;
    }

    public function build(): Weight
    {
        return $this->weight;
    }
}
