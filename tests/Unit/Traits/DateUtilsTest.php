<?php

namespace App\Tests\Unit\Traits;

use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;

class DateUtilsTest extends TestCase
{
    use DateUtils;

    public function testFormatDateTime()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 12:12:12');
        $formatted = $this->formatDate($date);

        $this->assertEquals('2020-01-01 12:12:12Z', $formatted);
    }

    public function testFormatDate()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 12:12:12');
        $formatted = $this->formatDate($date, $this->dateFormat);

        $this->assertEquals('2020-01-01', $formatted);
    }

    public function testTimeZone()
    {
        $timeZone = $this->getUTCTimeZone();

        $this->assertEquals('UTC', $timeZone->getName());
    }

    public function testCreateFromFormat()
    {
        $date = $this->createFromFormat('2020-01-01 12:12:12');

        $this->assertEquals('2020-01-01 12:12:12Z', $this->formatDate($date));
    }

    public function testCreateFromFormatWithoutTime()
    {
        $date = $this->createFromFormat('2020-01-01', $this->dateFormat);

        $this->assertEquals('2020-01-01', $this->formatDate($date, $this->dateFormat));
    }

    public function testCreateFromFormatRoundHours()
    {
        $date = $this->createFromFormat('2020-01-01 12:12:12', $this->dateTimeFormat, null, true);

        $this->assertEquals('2020-01-01 00:00:00Z', $this->formatDate($date));
    }
}
