<?php

namespace App\Domain\Nutrition\Enums;

enum MealTime: string
{
    case Breakfast = 'breakfast';
    case SecondBreakfast = 'second_breakfast';
    case Lunch = 'lunch';
    case AfternoonSnack = 'afternoon_snack';
    case Dinner = 'dinner';

    /**
     * Share of the daily calorie target this meal time should account for.
     * The five shares sum to 1.0.
     */
    public function calorieShare(): float
    {
        return match ($this) {
            self::Breakfast => 0.25,
            self::SecondBreakfast => 0.10,
            self::Lunch => 0.30,
            self::AfternoonSnack => 0.10,
            self::Dinner => 0.25,
        };
    }
}
