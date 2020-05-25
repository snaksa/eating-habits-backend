<?php

namespace App\DataFixtures;

use App\Constant\MealTypes;
use App\Entity\Meal;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MealFixtures extends Fixture implements DependentFixtureInterface
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        for ($j = 0; $j < 2; $j++) {
            $date = $this->getCurrentDateTime()->modify('- 6 days')->setTime(9, 0, 0);
            for ($i = 0; $i < 50; $i++) {
                $meal = new Meal();
                $meal->setDescription("Meal {$j}{$i}");
                $meal->setType(MealTypes::SNACK);
                $meal->setPicture('/picture/path');
                $meal->setDate($date);
                $meal->setUser($this->getReference("user_demo" . ($j === 0 ? '' : '2')));
                $manager->persist($meal);
                $this->setReference("user_demo_meal_{$j}_{$i}", $meal);

                $date = (clone $date)->modify('+ 3 hours');
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
