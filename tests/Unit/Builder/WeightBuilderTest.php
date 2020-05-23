<?php

namespace App\Tests\Unit\Builder;

use App\Builder\WeightBuilder;
use App\Entity\User;
use App\Entity\Weight;
use App\Exception\InvalidDateException;
use App\Request\Weight\CreateWeightRequest;
use App\Request\Weight\UpdateWeightRequest;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class WeightBuilderTest extends TestCase
{
    use DateUtils;

    public function test_weight_builder_create()
    {
        $user = (new User())->setId(1);
        $service = new WeightBuilder();

        $weight = $service
            ->create()
            ->setUser($user)
            ->build();

        $this->assertEquals($user, $weight->getUser());
    }

    public function test_weight_builder_bind_create_request()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateWeightRequest(new Request());
        $request->weight = 80;
        $request->date = $date->format('Y-m-d H:i:s');

        $service = new WeightBuilder();

        $weight = $service
            ->create()
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $weight->getUser());
        $this->assertEquals(80, $weight->getWeight());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $weight->getDate()->format('Y-m-d H:i:s'));
    }

    public function test_weight_builder_bind_create_request_invalid_date_exception()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();

        $request = new CreateWeightRequest(new Request());
        $request->weight = 80;
        $request->date = $date->format('Y-m-d H:i');

        $service = new WeightBuilder();

        $this->expectException(InvalidDateException::class);

        $service->create()
            ->setUser($user)
            ->bind($request)
            ->build();
    }

    public function test_weight_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $date = $this->getCurrentDateTime();
        $weight = (new Weight())
            ->setId(1)
            ->setWeight(70)
            ->setDate($this->getCurrentDateTime()->modify('- 2 days'));

        $request = new UpdateWeightRequest(new Request());
        $request->weight = 80;
        $request->date = $date->format('Y-m-d H:i:s');

        $service = new WeightBuilder();

        $weight = $service
            ->setWeight($weight)
            ->setUser($user)
            ->bind($request)
            ->build();

        $this->assertEquals($user, $weight->getUser());
        $this->assertEquals(80, $weight->getWeight());
        $this->assertEquals($date->format('Y-m-d H:i:s'), $weight->getDate()->format('Y-m-d H:i:s'));
    }
}
