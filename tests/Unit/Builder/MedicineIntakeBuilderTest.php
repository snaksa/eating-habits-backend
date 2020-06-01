<?php

namespace App\Tests\Unit\Builder;

use App\Builder\MedicineIntakeBuilder;
use App\Entity\MedicineIntake;
use App\Entity\MedicineSchedule;
use App\Exception\InvalidDataException;
use App\Exception\InvalidDateException;
use App\Repository\MedicineIntakeRepository;
use App\Repository\MedicineScheduleRepository;
use App\Request\MedicineIntake\CreateMedicineIntakeRequest;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class MedicineIntakeBuilderTest extends TestCase
{
    use DateUtils;

    public function test_medicine_intake_builder_create()
    {
        $medicineSchedule = (new MedicineSchedule())->setId(1);

        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $service = new MedicineIntakeBuilder($medicineIntakeRepositoryMock, $medicineScheduleRepositoryMock);

        $medicineIntake = $service
            ->create()
            ->setMedicineSchedule($medicineSchedule)
            ->build();

        $this->assertEquals($medicineSchedule, $medicineIntake->getMedicineSchedule());
    }

    public function test_medicine_intake_builder_bind_create_request()
    {
        $medicineSchedule = (new MedicineSchedule())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineIntakeRequest(new Request());
        $request->medicineScheduleId = 1;
        $request->date = $date->format('Y-m-d H:i:s');

        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findExistingIntake')->willReturn(null);
        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicineSchedule);
        $service = new MedicineIntakeBuilder($medicineIntakeRepositoryMock, $medicineScheduleRepositoryMock);

        $medicineIntake = $service
            ->create()
            ->bind($request)
            ->build();

        $this->assertEquals($medicineSchedule, $medicineIntake->getMedicineSchedule());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $medicineIntake->getDate()->format('Y-m-d H:i:s'));
    }

    public function test_medicine_intake_builder_bind_create_request_invalid_date_exception()
    {
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineIntakeRequest(new Request());
        $request->medicineScheduleId = 1;
        $request->date = $date->format('Y-m-d');


        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $service = new MedicineIntakeBuilder($medicineIntakeRepositoryMock, $medicineScheduleRepositoryMock);

        $this->expectException(InvalidDateException::class);

        $service->create()
            ->bind($request)
            ->build();
    }

    public function test_medicine_intake_builder_bind_create_request_medicine_schedule_not_found()
    {
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineIntakeRequest(new Request());
        $request->medicineScheduleId = 1;
        $request->date = $date->format('Y-m-d H:i:s');

        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn(null);
        $service = new MedicineIntakeBuilder($medicineIntakeRepositoryMock, $medicineScheduleRepositoryMock);

        $this->expectException(InvalidDataException::class);

        $service->create()
            ->bind($request)
            ->build();
    }

    public function test_medicine_intake_builder_bind_create_request_record_already_exists()
    {
        $medicineSchedule = (new MedicineSchedule())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateMedicineIntakeRequest(new Request());
        $request->medicineScheduleId = 1;
        $request->date = $date->format('Y-m-d H:i:s');


        $medicineIntakeRepositoryMock = $this->createMock(MedicineIntakeRepository::class);
        $medicineIntakeRepositoryMock->expects($this->once())->method('findExistingIntake')->willReturn(new MedicineIntake());
        $medicineScheduleRepositoryMock = $this->createMock(MedicineScheduleRepository::class);
        $medicineScheduleRepositoryMock->expects($this->once())->method('findOneById')->willReturn($medicineSchedule);
        $service = new MedicineIntakeBuilder($medicineIntakeRepositoryMock, $medicineScheduleRepositoryMock);

        $this->expectException(InvalidDataException::class);

        $service
            ->create()
            ->bind($request)
            ->build();
    }
}
