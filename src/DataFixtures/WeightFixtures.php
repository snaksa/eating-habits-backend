<?php

namespace App\DataFixtures;

use App\Entity\Weight;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WeightFixtures extends Fixture
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        for ($j = 0; $j < 2; $j++) {
            $date = $this->getCurrentDateTime()->modify('- 9 days')->setTime(12, 0, 0);
            for ($i = 0; $i < 10; $i++) {
                $weight = new Weight();
                $weight->setWeight(rand(80, 85) + (rand(1, 9) / 10));
                $weight->setDate($date);
                $weight->setUser($this->getReference("user_demo" . ($j === 0 ? '' : '2')));
                $manager->persist($weight);
                $this->setReference("user_demo_water_supply_{$i}", $weight);

                $date = (clone $date)->modify('+ 24 hours');
            }
        }

        $manager->flush();
    }
}
