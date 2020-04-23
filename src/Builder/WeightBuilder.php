<?php

namespace App\Builder;

use App\Entity\User;
use App\Entity\Weight;
use App\Exception\EntityNotFoundException;
use App\Exception\InvalidDateException;
use App\Repository\UserRepository;
use App\Request\Weight\CreateWeightRequest;
use App\Request\Weight\UpdateWeightRequest;
use App\Traits\DateUtils;

class WeightBuilder extends BaseBuilder
{
    use DateUtils;

    private Weight $weight;

    private UserRepository $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;
    }

    public function create(): self {
        $this->weight = new Weight();

        return $this;
    }

    /**
     * @param CreateWeightRequest|UpdateWeightRequest $input
     * @return $this
     * @throws EntityNotFoundException
     * @throws InvalidDateException
     */
    public function bind($input): self {

        if($input->date !== null) {
            $date = $this->createFromFormat($input->date, $this->dateTimeFormat);

            if(!$date) {
                throw new InvalidDateException('Date is not valid');
            }

            $this->setDate($date);
        }

        if($input->weight !== null) {
            $this->setWeightAmount($input->weight);
        }

        if(property_exists($input, 'userId') && $input->userId !== null) {
            $user = $this->findEntity($input->userId, $this->userRepository);

            if(!$user) {
                throw new EntityNotFoundException("User with ID {$input->userId} was not found");
            }

            $this->setUser($user);
        }

        return $this;
    }

    public function setWeight(Weight $weight): self {
        $this->weight = $weight;

        return $this;
    }

    public function setDate(\DateTimeInterface $date) {
        $this->weight->setDate($date);
    }

    public function setWeightAmount(string $weight): self {
        $this->weight->setWeight($weight);

        return $this;
    }

    public function setUser(User $user): self {
        $this->weight->setUser($user);

        return $this;
    }

    public function build(): Weight {
        return $this->weight;
    }
}
