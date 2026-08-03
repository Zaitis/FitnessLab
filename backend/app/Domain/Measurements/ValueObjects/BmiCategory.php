<?php

namespace App\Domain\Measurements\ValueObjects;

enum BmiCategory: string
{
    case Underweight = 'underweight';
    case Normal = 'normal';
    case Overweight = 'overweight';
    case Obese = 'obese';

    public static function forValue(float $bmi): self
    {
        return match (true) {
            $bmi < 18.5 => self::Underweight,
            $bmi < 25.0 => self::Normal,
            $bmi < 30.0 => self::Overweight,
            default => self::Obese,
        };
    }
}
