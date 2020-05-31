<?php

namespace App\Constant;

class MedicineFrequencies
{
    const EVERYDAY = 1;
    const PERIOD = 2;
    const ONCE = 3;

    public static function all()
    {
        return [
            'Everyday' => self::EVERYDAY,
            'Period' => self::PERIOD,
            'Once' => self::ONCE
        ];
    }
}
