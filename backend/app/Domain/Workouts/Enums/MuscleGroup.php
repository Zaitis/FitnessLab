<?php

namespace App\Domain\Workouts\Enums;

enum MuscleGroup: string
{
    case Chest = 'chest';
    case Back = 'back';
    case Legs = 'legs';
    case Shoulders = 'shoulders';
    case Arms = 'arms';
    case Core = 'core';
}
