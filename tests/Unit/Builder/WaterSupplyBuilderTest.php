<?php

namespace App\Tests\Unit\Builder;

use App\Builder\WaterSupplyBuilder;
use App\Entity\User;
use App\Entity\WaterSupply;
use App\Exception\InvalidDateException;
use App\Request\WaterSupply\CreateWaterSupplyRequest;
use App\Request\WaterSupply\UpdateWaterSupplyRequest;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class WaterSupplyBuilderTest extends TestCase
{
    use DateUtils;

    public function test_water_supply_builder_create()
    {
        $user = (new User())->setId(1);

        $service = new WaterSupplyBuilder();

        $waterSupply = $service
            ->create()
            ->setUser($user)
            ->build();

        $this->assertEquals($user, $waterSupply->getUser());
    }

    public function test_water_supply_builder_bind_create_request()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateWaterSupplyRequest(new Request());
        $request->amount = 250;
        $request->date = $date->format('Y-m-d H:i:s');

        $service = new WaterSupplyBuilder();

        $waterSupply = $service
            ->create()
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $waterSupply->getUser());
        $this->assertEquals(250, $waterSupply->getAmount());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $waterSupply->getDate()->format('Y-m-d H:i:s'));
    }

    public function test_water_supply_builder_bind_create_request_invalid_date_exception()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateWaterSupplyRequest(new Request());
        $request->amount = 250;
        $request->date = $date->format('Y-m-d H:i');

        $service = new WaterSupplyBuilder();

        $this->expectException(InvalidDateException::class);

        $service->create()
            ->setUser($user)
            ->bind($request)
            ->build();
    }

    public function test_water_supply_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();
        $waterSupply = (new WaterSupply())
            ->setId(1)
            ->setAmount(250)
            ->setDate($this->getCurrentDateTime()->modify('- 2 days'));

        $request = new UpdateWaterSupplyRequest(new Request());
        $request->amount = 250;
        $request->date = $date->format('Y-m-d H:i:s');

        $service = new WaterSupplyBuilder();

        $waterSupply = $service
            ->setWaterSupply($waterSupply)
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $waterSupply->getUser());
        $this->assertEquals(250, $waterSupply->getAmount());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $waterSupply->getDate()->format('Y-m-d H:i:s'));
    }
}
