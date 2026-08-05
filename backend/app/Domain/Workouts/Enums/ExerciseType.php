<?php

namespace App\Domain\Workouts\Enums;

enum ExerciseType: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';

    /**
     * Owned here rather than duplicated wherever this type is displayed —
     * mirrors the frontend's react-i18next copy for the PDF export.
     */
    public function label(string $locale): string
    {
        return match ($locale) {
            'pl' => match ($this) {
                self::Strength => 'Siłowe',
                self::Cardio => 'Cardio',
            },
            default => match ($this) {
                self::Strength => 'Strength',
                self::Cardio => 'Cardio',
            },
        };
    }
}
