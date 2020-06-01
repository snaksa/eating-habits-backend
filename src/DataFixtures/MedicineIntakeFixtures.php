<?php

namespace App\DataFixtures;

use App\Entity\MedicineIntake;
use App\Traits\DateUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MedicineIntakeFixtures extends Fixture implements DependentFixtureInterface
{
    use DateUtils;

    public function load(ObjectManager $manager)
    {
        $date = $this->getCurrentDateTime();

        $medicineIntake = new MedicineIntake();
        $medicineIntake->setDate($date);
        $medicineIntake->setMedicineSchedule($this->getReference("user_demo_medicine_schedule_1"));
        $manager->persist($medicineIntake);
        $this->setReference("user_demo_medicine_intake_1", $medicineIntake);

        $medicineIntake = new MedicineIntake();
        $medicineIntake->setDate($date);
        $medicineIntake->setMedicineSchedule($this->getReference("user_demo_medicine_schedule_2"));
        $manager->persist($medicineIntake);
        $this->setReference("user_demo_medicine_intake_2", $medicineIntake);

        $medicineIntake = new MedicineIntake();
        $medicineIntake->setDate((clone $date)->modify('- 1 day'));
        $medicineIntake->setMedicineSchedule($this->getReference("user_demo_medicine_schedule_3"));
        $manager->persist($medicineIntake);
        $this->setReference("user_demo_medicine_intake_3", $medicineIntake);

        $medicineIntake = new MedicineIntake();
        $medicineIntake->setDate($date);
        $medicineIntake->setMedicineSchedule($this->getReference("user_demo_medicine_schedule_3"));
        $manager->persist($medicineIntake);
        $this->setReference("user_demo_medicine_intake_3_1", $medicineIntake);

        $medicineIntake = new MedicineIntake();
        $medicineIntake->setDate($date);
        $medicineIntake->setMedicineSchedule($this->getReference("user_demo_medicine_schedule_4"));
        $manager->persist($medicineIntake);
        $this->setReference("user_demo_medicine_intake_4", $medicineIntake);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            MedicineScheduleFixtures::class
        ];
    }
}
