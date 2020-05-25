<?php

namespace App\DataFixtures;

use App\Entity\WaterSupply;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WaterSupplyFixtures extends Fixture implements DependentFixtureInterface
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
                $this->setReference("user_demo_water_supply_{$j}_{$i}", $waterSupply);

                $date = (clone $date)->modify('+ 3 hours');
            }
        }

        $date = (new \DateTime())->setDate(2019, 12, 11)->setTime(20, 30, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_0", $waterSupply);

        $date = (new \DateTime())->setDate(2019, 12, 11)->setTime(21, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_1", $waterSupply);

        $date = (new \DateTime())->setDate(2019, 12, 12)->setTime(10, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_2", $waterSupply);

        $date = (new \DateTime())->setDate(2019, 12, 12)->setTime(13, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_3", $waterSupply);

        $date = (new \DateTime())->setDate(2019, 12, 12)->setTime(18, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_4", $waterSupply);

        $date = $this->getCurrentDateTime()->modify('- 1 days')->setTime(17, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_5", $waterSupply);

        $date = $this->getCurrentDateTime()->modify('- 1 days')->setTime(18, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_6", $waterSupply);

        $date = $this->getCurrentDateTime()->setTime(18, 0, 0);
        $waterSupply = new WaterSupply();
        $waterSupply->setAmount(250);
        $waterSupply->setDate($date);
        $waterSupply->setUser($this->getReference("user_demo3"));
        $manager->persist($waterSupply);
        $this->setReference("user_demo3_water_supply_7", $waterSupply);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
