<?php

namespace App\Domain\Nutrition\Criteria;

use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use App\Domain\Nutrition\Enums\Goal;

/**
 * Weight, height, age, sex, and activity level come from the user's latest
 * BmiMeasurement — not re-collected here — which is why this reuses
 * App\Domain\Measurements' Sex and ActivityLevel enums rather than
 * duplicating them the way Goal is deliberately duplicated from Workouts.
 * Those are plain typed data, not another module's Eloquent model, so
 * reusing them doesn't violate the cross-module boundary rule.
 */
final readonly class NutritionPlanCriteria
{
    public function __construct(
        public Goal $goal,
        public float $weightKg,
        public float $heightCm,
        public int $age,
        public Sex $sex,
        public ActivityLevel $activityLevel,
    ) {}

    /**
     * Estimated total daily energy expenditure via the Mifflin-St Jeor
     * formula (BMR) times the activity multiplier — a standard, reasonably
     * accurate estimate given only weight, height, age, sex, and activity
     * level, with no lab measurement of body composition.
     */
    public function estimatedTdee(): float
    {
        $bmr = 10 * $this->weightKg + 6.25 * $this->heightCm - 5 * $this->age
            + match ($this->sex) {
                Sex::Male => 5,
                Sex::Female => -161,
            };

        return $bmr * $this->activityLevel->tdeeMultiplier();
    }
}
