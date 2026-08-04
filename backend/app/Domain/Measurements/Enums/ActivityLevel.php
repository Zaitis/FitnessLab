<?php

namespace App\Domain\Measurements\Enums;

enum ActivityLevel: string
{
    case Sedentary = 'sedentary';
    case Light = 'light';
    case Moderate = 'moderate';
    case Active = 'active';
    case VeryActive = 'very_active';

    /**
     * The standard activity multiplier applied to BMR to estimate total
     * daily energy expenditure (TDEE) — owned here rather than duplicated
     * wherever a calorie target is computed, the same reasoning as
     * BmiCategory owning its thresholds.
     */
    public function tdeeMultiplier(): float
    {
        return match ($this) {
            self::Sedentary => 1.2,
            self::Light => 1.375,
            self::Moderate => 1.55,
            self::Active => 1.725,
            self::VeryActive => 1.9,
        };
    }
}
