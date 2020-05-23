<?php

namespace App\Builder;

use App\Constant\MealTypes;
use App\Entity\Meal;
use App\Entity\User;
use App\Exception\EntityNotFoundException;
use App\Exception\InvalidDateException;
use App\Exception\InvalidMealTypeException;
use App\Repository\UserRepository;
use App\Request\Meal\CreateMealRequest;
use App\Request\Meal\UpdateMealRequest;
use App\Traits\DateUtils;

class MealBuilder extends BaseBuilder
{
    use DateUtils;

    private Meal $meal;

    public function create(): self
    {
        $this->meal = new Meal();

        return $this;
    }

    /**
     * @param CreateMealRequest|UpdateMealRequest $input
     * @return $this
     * @throws InvalidDateException
     * @throws InvalidMealTypeException
     */
    public function bind($input): self
    {
        if ($input->description !== null) {
            $this->setDescription($input->description);
        }

        if ($input->picture !== null) {
            // TODO: upload picture
            $this->setPicture($input->picture);
        }

        if ($input->type !== null) {
            if (!in_array($input->type, MealTypes::all())) {
                throw new InvalidMealTypeException("Meal Type {$input->type} does not exist");
            }

            $this->setType($input->type);
        }

        if ($input->date !== null) {
            $date = $this->createFromFormat($input->date, $this->dateTimeFormat);

            if (!$date) {
                throw new InvalidDateException('Date is not valid');
            }

            $this->setDate($date);
        }

        return $this;
    }

    public function setMeal(Meal $meal): self
    {
        $this->meal = $meal;

        return $this;
    }

    public function setDate(\DateTimeInterface $date)
    {
        $this->meal->setDate($date);
    }

    public function setDescription(string $description): self
    {
        $this->meal->setDescription($description);

        return $this;
    }

    public function setType(int $type): self
    {
        $this->meal->setType($type);

        return $this;
    }

    public function setPicture(string $picture)
    {
        $this->meal->setPicture($picture);
    }

    public function setUser(User $user): self
    {
        $this->meal->setUser($user);

        return $this;
    }

    public function build(): Meal
    {
        return $this->meal;
    }
}
