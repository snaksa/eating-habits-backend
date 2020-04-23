<?php

namespace App\Constant;

class MealTypes
{
    const BREAKFAST = 1;
    const LUNCH = 2;
    const DINNER = 3;
    const SNACK = 4;

    public static function all()
    {
        return [
            'Breakfast' => self::BREAKFAST,
            'Lunch' => self::LUNCH,
            'Dinner' => self::DINNER,
            'Snack' => self::SNACK
        ];
    }
}
