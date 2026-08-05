<?php

namespace App\Domain\Workouts\Enums;

enum ExperienceLevel: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    /**
     * Ordinal rank used to filter exercises at or below a criteria's level —
     * a beginner plan should never include an advanced exercise.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Beginner => 1,
            self::Intermediate => 2,
            self::Advanced => 3,
        };
    }

    public function atMost(self $other): bool
    {
        return $this->rank() <= $other->rank();
    }

    /**
     * Owned here rather than duplicated wherever this level is displayed —
     * mirrors the frontend's react-i18next copy for the PDF export.
     */
    public function label(string $locale): string
    {
        return match ($locale) {
            'pl' => match ($this) {
                self::Beginner => 'Początkujący',
                self::Intermediate => 'Średniozaawansowany',
                self::Advanced => 'Zaawansowany',
            },
            default => match ($this) {
                self::Beginner => 'Beginner',
                self::Intermediate => 'Intermediate',
                self::Advanced => 'Advanced',
            },
        };
    }
}
