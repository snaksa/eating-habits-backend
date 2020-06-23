<?php

namespace App\Constant;

class Gender
{
    const MALE = 0;
    const FEMALE = 1;

    public static function all()
    {
        return [
            'Male' => self::MALE,
            'Female' => self::FEMALE
        ];
    }
}
