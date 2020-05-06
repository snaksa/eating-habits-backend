<?php

namespace App\DataFixtures;

use App\Entity\WaterSupply;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WaterSupplyFixtures extends Fixture
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        for ($j = 0; $j < 2; $j++) {
            $date = $this->getCurrentDateTime()->modify('- 6 days')->setTime(9, 0, 0);
            for ($i = 0; $i < 50; $i++) {
                $waterSupply = new WaterSupply();
                $waterSupply->setAmount(rand(250, 600));
                $waterSupply->setDate($date);
                $waterSupply->setUser($this->getReference("user_demo" . ($j === 0 ? '' : '2')));
                $manager->persist($waterSupply);
                $this->setReference("user_demo_water_supply_{$i}", $waterSupply);

                $date = (clone $date)->modify('+ 3 hours');
            }
        }

        $manager->flush();
    }
}
