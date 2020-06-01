<?php

namespace App\DataFixtures;

use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MedicineFixtures extends Fixture implements DependentFixtureInterface
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        $medicine = new Medicine();
        $medicine->setFrequency(MedicineFrequencies::EVERYDAY);
        $medicine->setName('Medicine 1 - Everyday');
        $medicine->setUser($this->getReference("user_demo"));
        $manager->persist($medicine);
        $this->setReference("user_demo_medicine_1", $medicine);

        $medicine = new Medicine();
        $medicine->setFrequency(MedicineFrequencies::ONCE);
        $medicine->setName('Medicine 2 - Once');
        $medicine->setUser($this->getReference("user_demo"));
        $manager->persist($medicine);
        $this->setReference("user_demo_medicine_2", $medicine);

        $medicine = new Medicine();
        $medicine->setFrequency(MedicineFrequencies::PERIOD);
        $medicine->setName('Medicine 3 - Period');
        $medicine->setUser($this->getReference("user_demo"));
        $manager->persist($medicine);
        $this->setReference("user_demo_medicine_3", $medicine);

        $medicine = new Medicine();
        $medicine->setFrequency(MedicineFrequencies::EVERYDAY);
        $medicine->setName('Medicine 4 - Everyday');
        $medicine->setUser($this->getReference("user_demo2"));
        $manager->persist($medicine);
        $this->setReference("user_demo_medicine_4", $medicine);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
