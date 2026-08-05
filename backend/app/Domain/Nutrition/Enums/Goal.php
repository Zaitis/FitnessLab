<?php

namespace App\Domain\Nutrition\Enums;

/**
 * Deliberately not shared with App\Domain\Workouts\Enums\Goal, even though
 * the cases are identical — the two goal hierarchies are parallel, not
 * merged, per docs/DESIGN-PATTERNS.md §1: training and nutrition are
 * different domains that coincidentally rhyme.
 */
enum Goal: string
{
    case FatLoss = 'fat_loss';
    case MuscleGain = 'muscle_gain';
    case Maintenance = 'maintenance';

    /**
     * Owned here rather than duplicated wherever a goal name is displayed —
     * mirrors the frontend's react-i18next copy for the one surface the
     * backend renders itself (the PDF export).
     */
    public function label(string $locale): string
    {
        return match ($locale) {
            'pl' => match ($this) {
                self::FatLoss => 'Redukcja tkanki tłuszczowej',
                self::MuscleGain => 'Budowa masy mięśniowej',
                self::Maintenance => 'Utrzymanie formy',
            },
            default => match ($this) {
                self::FatLoss => 'Fat loss',
                self::MuscleGain => 'Muscle gain',
                self::Maintenance => 'Maintenance',
            },
        };
    }
}
