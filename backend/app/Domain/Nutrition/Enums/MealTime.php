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

    /**
     * Owned here rather than duplicated wherever this meal time is
     * displayed — mirrors the frontend's react-i18next copy for the PDF
     * export.
     */
    public function label(string $locale): string
    {
        return match ($locale) {
            'pl' => match ($this) {
                self::Breakfast => 'Śniadanie',
                self::SecondBreakfast => 'Drugie śniadanie',
                self::Lunch => 'Obiad',
                self::AfternoonSnack => 'Podwieczorek',
                self::Dinner => 'Kolacja',
            },
            default => match ($this) {
                self::Breakfast => 'Breakfast',
                self::SecondBreakfast => 'Second breakfast',
                self::Lunch => 'Lunch',
                self::AfternoonSnack => 'Afternoon snack',
                self::Dinner => 'Dinner',
            },
        };
    }
}
