<?php

namespace App\Tests\Unit\Builder;

use App\Builder\MedicineBuilder;
use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\User;
use App\Exception\InvalidMedicineFrequencyException;
use App\Request\Medicine\CreateMedicineRequest;
use App\Request\Medicine\UpdateMedicineRequest;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MedicineBuilderTest extends TestCase
{
    use DateUtils;

    public function test_medicine_builder_create()
    {
        $user = (new User())->setId(1);

        $service = new MedicineBuilder();

        $medicine = $service
            ->create()
            ->setUser($user)
            ->build();

        $this->assertEquals($user, $medicine->getUser());
    }

    public function test_medicine_builder_bind_create_request()
    {
        $user = (new User())->setId(1);

        $request = new CreateMedicineRequest(new Request());
        $request->name = 'test';
        $request->image = 'picture';
        $request->frequency = MedicineFrequencies::EVERYDAY;

        $service = new MedicineBuilder();

        $medicine = $service
            ->create()
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $medicine->getUser());
        $this->assertEquals('test', $medicine->getName());
        $this->assertEquals('picture', $medicine->getImage());
        $this->assertEquals(MedicineFrequencies::EVERYDAY, $medicine->getFrequency());
    }

    public function test_medicine_builder_bind_create_request_invalid_frequency()
    {
        $user = (new User())->setId(1);

        $request = new CreateMedicineRequest(new Request());
        $request->name = 'test';
        $request->image = 'picture';
        $request->frequency = -1;

        $service = new MedicineBuilder();

        $this->expectException(InvalidMedicineFrequencyException::class);

        $service->create()
            ->setUser($user)
            ->bind($request)
            ->build();
    }

    public function test_medicine_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $medicine = (new Medicine())
            ->setId(1)
            ->setName('test1')
            ->setImage('picture1')
            ->setFrequency(MedicineFrequencies::EVERYDAY);

        $request = new UpdateMedicineRequest(new Request());
        $request->name = 'test2';
        $request->image = 'picture2';
        $request->frequency = MedicineFrequencies::PERIOD;

        $service = new MedicineBuilder();

        $medicine = $service
            ->setMedicine($medicine)
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $medicine->getUser());
        $this->assertEquals('test2', $medicine->getName());
        $this->assertEquals('picture2', $medicine->getImage());
        $this->assertEquals(MedicineFrequencies::PERIOD, $medicine->getFrequency());
    }
}
