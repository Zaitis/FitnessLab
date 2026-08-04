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
}
