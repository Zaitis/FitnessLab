<?php

use App\Domain\Measurements\ValueObjects\BmiCategory;

it('assigns the correct category', function (float $bmi, BmiCategory $expected) {
    expect(BmiCategory::forValue($bmi))->toBe($expected);
})->with([
    'just under underweight boundary' => [18.4, BmiCategory::Underweight],
    'at the normal boundary' => [18.5, BmiCategory::Normal],
    'just under overweight boundary' => [24.9, BmiCategory::Normal],
    'at the overweight boundary' => [25.0, BmiCategory::Overweight],
    'just under obese boundary' => [29.9, BmiCategory::Overweight],
    'at the obese boundary' => [30.0, BmiCategory::Obese],
    'well underweight' => [12.0, BmiCategory::Underweight],
    'well obese' => [45.0, BmiCategory::Obese],
]);
