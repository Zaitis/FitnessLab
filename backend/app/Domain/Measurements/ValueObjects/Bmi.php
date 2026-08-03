<?php

namespace App\Domain\Measurements\ValueObjects;

final readonly class Bmi
{
    private function __construct(
        public float $value,
        public BmiCategory $category,
    ) {}

    public static function fromMeasurements(Weight $weight, Height $height): self
    {
        $heightInMeters = $height->centimeters / 100;
        $value = round($weight->kilograms / ($heightInMeters ** 2), 1);

        return new self($value, BmiCategory::forValue($value));
    }
}
