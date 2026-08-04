<?php

namespace App\Application\Measurements\Actions;

use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;
use App\Models\BmiMeasurement;
use App\Models\User;

final class RecordMeasurementAction
{
    public function __construct(private readonly CalculateBmiAction $calculateBmi) {}

    public function execute(
        User $user,
        Weight $weight,
        Height $height,
        int $age,
        Sex $sex,
        ActivityLevel $activityLevel,
    ): BmiMeasurement {
        $bmi = $this->calculateBmi->execute($weight, $height);

        return BmiMeasurement::create([
            'user_id' => $user->id,
            'weight_kg' => $weight->kilograms,
            'height_cm' => $height->centimeters,
            'age' => $age,
            'sex' => $sex->value,
            'activity_level' => $activityLevel->value,
            'bmi_value' => $bmi->value,
            'category' => $bmi->category->value,
            'measured_on' => now()->toDateString(),
        ]);
    }
}
