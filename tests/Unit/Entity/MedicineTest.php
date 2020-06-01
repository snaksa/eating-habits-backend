<?php

namespace App\Tests\Unit\Entity;

use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\MedicineSchedule;
use App\Entity\User;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class MedicineTest extends TestCase
{
    use DateUtils;

    public function testGettersAndSetters()
    {
        $user = (new User())
            ->setUsername('test@gmail.com')
            ->setPassword('password')
            ->setRoles(['ROLE_USER']);

        $medicine = (new Medicine())
            ->setId(1)
            ->setName('Medicine 1')
        ->setFrequency(MedicineFrequencies::EVERYDAY)
        ->setImage('image')
        ->setUser($user);

        $this->assertEquals(1, $medicine->getId());
        $this->assertEquals(MedicineFrequencies::EVERYDAY, $medicine->getFrequency());
        $this->assertEquals('Medicine 1', $medicine->getName());
        $this->assertEquals('image', $medicine->getImage());
        $this->assertEquals($user, $medicine->getUser());

        $date = $this->getCurrentDateTime();

        $medicineSchedule = (new MedicineSchedule())->setIntakeTime($date);
        $medicine->addSchedule($medicineSchedule);
        $this->assertEquals(1, $medicine->getSchedule()->count());
        $medicine->removeSchedule($medicineSchedule);
        $this->assertEquals(0, $medicine->getSchedule()->count());
    }
}
