<?php

namespace App\DataFixtures;

use App\Entity\MedicineSchedule;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MedicineScheduleFixtures extends Fixture implements DependentFixtureInterface
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        $date = $this->getCurrentDateTime();

        $medicineSchedule = new MedicineSchedule();
        $medicineSchedule->setIntakeTime($date);
        $medicineSchedule->setMedicine($this->getReference("user_demo_medicine_1"));
        $manager->persist($medicineSchedule);
        $this->setReference("user_demo_medicine_schedule_1", $medicineSchedule);

        $medicineSchedule = new MedicineSchedule();
        $medicineSchedule->setIntakeTime($date);
        $medicineSchedule->setMedicine($this->getReference("user_demo_medicine_2"));
        $manager->persist($medicineSchedule);
        $this->setReference("user_demo_medicine_schedule_2", $medicineSchedule);

        $medicineSchedule = new MedicineSchedule();
        $medicineSchedule->setIntakeTime((clone $date)->modify('- 5 days'));
        $medicineSchedule->setPeriodSpan(24*60*60); // one day
        $medicineSchedule->setMedicine($this->getReference("user_demo_medicine_3"));
        $manager->persist($medicineSchedule);
        $this->setReference("user_demo_medicine_schedule_3", $medicineSchedule);

        $medicineSchedule = new MedicineSchedule();
        $medicineSchedule->setIntakeTime($date);
        $medicineSchedule->setMedicine($this->getReference("user_demo_medicine_4"));
        $manager->persist($medicineSchedule);
        $this->setReference("user_demo_medicine_schedule_4", $medicineSchedule);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            MedicineFixtures::class
        ];
    }
}
