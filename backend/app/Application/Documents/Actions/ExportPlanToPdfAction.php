<?php

namespace App\Application\Documents\Actions;

use App\Domain\Documents\Contracts\PdfExporterInterface;
use App\Domain\Documents\ValueObjects\PdfDocument;
use App\Domain\Documents\ValueObjects\PlanExportData;
use App\Domain\Documents\ValueObjects\PlanExportSection;
use App\Domain\Nutrition\Enums\Goal as NutritionGoal;
use App\Domain\Nutrition\Enums\MealTime;
use App\Domain\Workouts\Enums\ExerciseType;
use App\Domain\Workouts\Enums\ExperienceLevel;
use App\Domain\Workouts\Enums\Goal as WorkoutGoal;
use App\Models\NutritionPlan;
use App\Models\WorkoutPlan;
use DateTimeImmutable;

final class ExportPlanToPdfAction
{
    public function __construct(private readonly PdfExporterInterface $exporter) {}

    public function execute(WorkoutPlan|NutritionPlan $plan): PdfDocument
    {
        $data = $plan instanceof WorkoutPlan
            ? $this->buildWorkoutExportData($plan)
            : $this->buildNutritionExportData($plan);

        return $this->exporter->export($data);
    }

    private function buildWorkoutExportData(WorkoutPlan $plan): PlanExportData
    {
        $locale = app()->getLocale();
        $fallback = config('supported_locales.default');
        $goal = WorkoutGoal::from($plan->goal);
        $experience = ExperienceLevel::from($plan->experience_level);

        return new PlanExportData(
            filename: "workout-plan-{$plan->id}.pdf",
            title: $goal->label($locale),
            summary: trans('plans.workout_summary', [
                'level' => $experience->label($locale),
                'days' => $plan->days_per_week,
            ], $locale),
            sections: $this->workoutSections($plan, $locale, $fallback),
            disclaimer: config("disclaimer.standard.{$locale}", config("disclaimer.standard.{$fallback}")),
            locale: $locale,
            generatedAt: new DateTimeImmutable,
        );
    }

    /**
     * @return list<PlanExportSection>
     */
    private function workoutSections(WorkoutPlan $plan, string $locale, string $fallback): array
    {
        $byDay = [];
        foreach ($plan->generated_plan as $item) {
            $byDay[$item['day']][] = $item;
        }
        ksort($byDay);

        $sections = [];
        foreach ($byDay as $day => $items) {
            $lines = array_map(function (array $item) use ($locale, $fallback) {
                $type = ExerciseType::from($item['type']);
                $name = $item['name'][$locale] ?? $item['name'][$fallback];
                $detail = $type === ExerciseType::Strength
                    ? trans('plans.sets_reps', ['sets' => $item['sets'], 'reps' => $item['reps']], $locale)
                    : trans('plans.duration_minutes', ['minutes' => $item['duration_minutes']], $locale);

                return sprintf('%s — %s: %s', $type->label($locale), $name, $detail);
            }, $items);

            $sections[] = new PlanExportSection(
                heading: trans('plans.day', ['day' => $day], $locale),
                lines: $lines,
            );
        }

        return $sections;
    }

    private function buildNutritionExportData(NutritionPlan $plan): PlanExportData
    {
        $locale = app()->getLocale();
        $fallback = config('supported_locales.default');
        $goal = NutritionGoal::from($plan->goal);

        return new PlanExportData(
            filename: "nutrition-plan-{$plan->id}.pdf",
            title: $goal->label($locale),
            summary: trans('plans.nutrition_summary', [
                'calories' => $plan->daily_calorie_target,
                'protein' => "{$plan->generated_plan['protein_g']}g",
                'fat' => "{$plan->generated_plan['fat_g']}g",
                'carbs' => "{$plan->generated_plan['carbs_g']}g",
            ], $locale),
            sections: $this->nutritionSections($plan, $locale, $fallback),
            disclaimer: config("disclaimer.standard.{$locale}", config("disclaimer.standard.{$fallback}")),
            locale: $locale,
            generatedAt: new DateTimeImmutable,
        );
    }

    /**
     * @return list<PlanExportSection>
     */
    private function nutritionSections(NutritionPlan $plan, string $locale, string $fallback): array
    {
        $byDay = [];
        foreach ($plan->generated_plan['items'] as $item) {
            $byDay[$item['day']][] = $item;
        }
        ksort($byDay);

        $sections = [];
        foreach ($byDay as $day => $items) {
            $lines = array_map(function (array $item) use ($locale, $fallback) {
                $mealTime = MealTime::from($item['meal_time']);
                $name = $item['name'][$locale] ?? $item['name'][$fallback];
                $calories = trans('plans.calories_suffix', ['calories' => $item['calories']], $locale);

                return sprintf('%s — %s (%s)', $mealTime->label($locale), $name, $calories);
            }, $items);

            $sections[] = new PlanExportSection(
                heading: trans('plans.day', ['day' => $day], $locale),
                lines: $lines,
            );
        }

        return $sections;
    }
}
