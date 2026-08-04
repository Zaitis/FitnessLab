<?php

namespace App\Application\Demo\Actions;

use App\Application\Measurements\Actions\CalculateBmiAction;
use App\Application\Nutrition\Actions\GenerateNutritionPlanAction;
use App\Application\Workouts\Actions\GenerateWorkoutPlanAction;
use App\Domain\Adherence\Enums\PlanType;
use App\Domain\Measurements\Enums\ActivityLevel;
use App\Domain\Measurements\Enums\Sex;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;
use App\Domain\Nutrition\Enums\Goal as NutritionGoal;
use App\Domain\Workouts\Criteria\WorkoutPlanCriteria;
use App\Domain\Workouts\Enums\ExerciseLocation;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\Goal as WorkoutGoal;
use App\Models\AdherenceEntry;
use App\Models\BmiMeasurement;
use App\Models\NutritionPlan;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Wipes and re-seeds the shared demo account to a known, populated state.
 * Called once at initial setup (DemoAccountSeeder) and nightly thereafter
 * (the demo:reset console command), so accumulated visitor edits never
 * degrade the experience for the next visitor. Idempotent by design — safe
 * to run any number of times, unlike the catalogue seeders it depends on.
 */
final class ResetDemoAccountAction
{
    public function __construct(
        private readonly CalculateBmiAction $calculateBmi,
        private readonly GenerateWorkoutPlanAction $generateWorkoutPlan,
        private readonly GenerateNutritionPlanAction $generateNutritionPlan,
    ) {}

    public function execute(): User
    {
        // updateOrCreate() fills through $fillable, which deliberately
        // excludes is_demo/is_admin (they must never be mass-assignable from
        // a request) — forceFill is what actually sets them here.
        $user = User::firstOrNew(['email' => config('demo.email')]);
        $user->forceFill([
            'name' => 'Demo Account',
            'password' => Hash::make(config('demo.password')),
            'email_verified_at' => now(),
            'locale' => null,
            'is_admin' => false,
            'is_demo' => true,
        ])->save();

        $user->adherenceEntries()->delete();
        $user->workoutPlans()->delete();
        $user->nutritionPlans()->delete();
        $user->bmiMeasurements()->delete();

        $this->seedMeasurementHistory($user);

        $workoutPlan = $this->generateWorkoutPlan->execute($user, new WorkoutPlanCriteria(
            goal: WorkoutGoal::FatLoss,
            experienceLevel: ExperienceLevel::Beginner,
            daysPerWeek: 3,
            location: ExerciseLocation::Gym,
        ));

        $nutritionPlan = $this->generateNutritionPlan->execute($user, NutritionGoal::FatLoss);

        $this->seedAdherence($user, $workoutPlan, $nutritionPlan);

        return $user;
    }

    /**
     * A gentle downward trend over three weeks — the progress chart should
     * show something happening, not a flat line or a single point. The last
     * entry (today) is also what GenerateNutritionPlanAction reads for age/
     * sex/activity level.
     */
    private function seedMeasurementHistory(User $user): void
    {
        $history = [
            ['daysAgo' => 21, 'weightKg' => 82.5],
            ['daysAgo' => 14, 'weightKg' => 81.0],
            ['daysAgo' => 7, 'weightKg' => 79.8],
            ['daysAgo' => 0, 'weightKg' => 78.5],
        ];

        foreach ($history as $entry) {
            $weight = new Weight($entry['weightKg']);
            $height = new Height(180.0);
            $bmi = $this->calculateBmi->execute($weight, $height);

            BmiMeasurement::create([
                'user_id' => $user->id,
                'weight_kg' => $weight->kilograms,
                'height_cm' => $height->centimeters,
                'age' => 32,
                'sex' => Sex::Male->value,
                'activity_level' => ActivityLevel::Moderate->value,
                'bmi_value' => $bmi->value,
                'category' => $bmi->category->value,
                'measured_on' => now()->subDays($entry['daysAgo'])->toDateString(),
            ]);
        }
    }

    /**
     * Both plans are generated moments ago, so today is always structural
     * day 1 of each plan's cycle (frontend/src/lib/adherence.ts's
     * planDayForDate maps a plan's own creation day to day 1) and yesterday
     * is always the last day of the cycle — no date-cycle math needed here,
     * only at the two calendar dates this seeder actually touches. Today is
     * left half-done and yesterday fully done: a believable demo narrative,
     * and it guarantees the calendar shows checked-off items the moment a
     * visitor opens it.
     */
    private function seedAdherence(User $user, WorkoutPlan $workoutPlan, NutritionPlan $nutritionPlan): void
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

        $this->checkOffDay($user, PlanType::Workout, $workoutPlan, $workoutPlan->days_per_week, $yesterday, 1.0);
        $this->checkOffDay($user, PlanType::Nutrition, $nutritionPlan, 7, $yesterday, 1.0);
        $this->checkOffDay($user, PlanType::Workout, $workoutPlan, 1, $today, 0.5);
        $this->checkOffDay($user, PlanType::Nutrition, $nutritionPlan, 1, $today, 0.5);
    }

    private function checkOffDay(
        User $user,
        PlanType $planType,
        WorkoutPlan|NutritionPlan $plan,
        int $day,
        Carbon $date,
        float $fraction,
    ): void {
        $itemIds = $plan->itemIdsForDay($day);
        $take = (int) ceil(count($itemIds) * $fraction);

        foreach (array_slice($itemIds, 0, $take) as $itemId) {
            AdherenceEntry::create([
                'user_id' => $user->id,
                'entry_date' => $date->toDateString(),
                'plan_type' => $planType->value,
                'plan_id' => $plan->id,
                'plan_item_id' => $itemId,
                'completed_at' => $date->copy()->setTimeFromTimeString('19:00'),
            ]);
        }
    }
}
