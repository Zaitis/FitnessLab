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
}
