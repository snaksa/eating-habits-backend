<?php

namespace App\Tests\Unit\Builder;

use App\Builder\MedicineScheduleBuilder;
use App\Constant\MedicineFrequencies;
use App\Entity\Medicine;
use App\Entity\MedicineSchedule;
use App\Exception\InvalidDataException;
use App\Exception\InvalidDateException;
use App\Repository\MedicineRepository;
use App\Request\MedicineSchedule\CreateMedicineScheduleRequest;
use App\Request\MedicineSchedule\UpdateMedicineScheduleRequest;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MedicineScheduleBuilderTest extends TestCase
{
    use DateUtils;

    public function test_medicine_schedule_builder_create()
    {
        $medicine = (new Medicine())->setId(1);

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $service = new MedicineScheduleBuilder($medicineRepository);

        $medicineSchedule = $service
            ->create()
            ->setMedicine($medicine)
            ->build();

        $this->assertEquals($medicine, $medicineSchedule->getMedicine());
    }

    public function test_medicine_schedule_builder_bind_create_request()
    {
        $medicine = (new Medicine())->setId(1)->setFrequency(MedicineFrequencies::EVERYDAY);
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineScheduleRequest(new Request());
        $request->medicineId = 1;
        $request->periodSpan = 100;
        $request->intakeTime = $date->format('Y-m-d H:i:s');

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $medicineRepository->expects($this->once())->method('findOneById')->willReturn($medicine);

        $service = new MedicineScheduleBuilder($medicineRepository);

        $medicineSchedule = $service
            ->create()
            ->bind($request)
            ->build();

        $this->assertEquals($medicine, $medicineSchedule->getMedicine());
        $this->assertEquals(100, $medicineSchedule->getPeriodSpan());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $medicineSchedule->getIntakeTime()->format('Y-m-d H:i:s'));
    }

    public function test_medicine_schedule_builder_bind_create_request_invalid_date_exception()
    {
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineScheduleRequest(new Request());
        $request->medicineId = 1;
        $request->periodSpan = 100;
        $request->intakeTime = $date->format('Y-m-d');

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $service = new MedicineScheduleBuilder($medicineRepository);

        $this->expectException(InvalidDateException::class);

        $service->create()
            ->bind($request)
            ->build();
    }

    public function test_medicine_schedule_builder_bind_create_request_medicine_not_found()
    {
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineScheduleRequest(new Request());
        $request->medicineId = 1;
        $request->periodSpan = 100;
        $request->intakeTime = $date->format('Y-m-d H:i:s');

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $medicineRepository->expects($this->once())->method('findOneById')->willReturn(null);

        $service = new MedicineScheduleBuilder($medicineRepository);
        $this->expectException(InvalidDataException::class);

        $service
            ->create()
            ->bind($request)
            ->build();
    }

    public function test_medicine_schedule_builder_bind_create_request_medicine_frequency_missing_period_span()
    {
        $medicine = (new Medicine())->setId(1)->setFrequency(MedicineFrequencies::PERIOD);
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineScheduleRequest(new Request());
        $request->medicineId = 1;
        $request->intakeTime = $date->format('Y-m-d H:i:s');

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $medicineRepository->expects($this->once())->method('findOneById')->willReturn($medicine);

        $service = new MedicineScheduleBuilder($medicineRepository);
        $this->expectException(InvalidDataException::class);

        $service
            ->create()
            ->bind($request)
            ->build();
    }

    public function test_medicine_schedule_builder_bind_update_request()
    {
        $medicine = (new Medicine())->setId(1)->setFrequency(MedicineFrequencies::PERIOD);
        $date = $this->getCurrentDateTime();
        $medicineSchedule = (new MedicineSchedule())
            ->setId(1)
            ->setIntakeTime($date)
            ->setPeriodSpan(100)
            ->setMedicine($medicine);

        $request = new UpdateMedicineScheduleRequest(new Request());
        $request->periodSpan = 200;
        $request->intakeTime = $date->modify('+ 1 day')->format('Y-m-d H:i:s');

        $medicineRepository = $this->createMock(MedicineRepository::class);
        $service = new MedicineScheduleBuilder($medicineRepository);

        $medicineSchedule = $service
            ->setMedicineSchedule($medicineSchedule)
            ->bind($request)
            ->build();

        $this->assertEquals(200, $medicineSchedule->getPeriodSpan());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $medicineSchedule->getIntakeTime()->format('Y-m-d H:i:s'));
    }
}
