<?php

namespace App\Domain\Adherence\Enums;

enum PlanType: string
{
    case Workout = 'workout';
    case Nutrition = 'nutrition';
}
