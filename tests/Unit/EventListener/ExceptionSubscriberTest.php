<?php

namespace App\Tests\Unit\EventListener;

use App\EventListener\ExceptionSubscriber;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriberTest extends TestCase
{
    use DateUtils;

    public function testEvents()
    {
        $this->assertEquals([KernelEvents::EXCEPTION => 'onException'], ExceptionSubscriber::getSubscribedEvents());
    }
}
