<?php

namespace App\Domain\Workouts\Enums;

enum Goal: string
{
    case FatLoss = 'fat_loss';
    case MuscleGain = 'muscle_gain';
    case Maintenance = 'maintenance';

    /**
     * Owned here rather than duplicated wherever a goal name is displayed —
     * the frontend has its own copy (react-i18next) for the dashboard UI,
     * but a server-rendered PDF export has no access to that, so the label
     * is mirrored here for the one surface the backend renders itself.
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
