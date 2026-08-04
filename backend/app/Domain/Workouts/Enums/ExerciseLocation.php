<?php

namespace App\Domain\Workouts\Enums;

enum ExerciseLocation: string
{
    case Gym = 'gym';
    case Home = 'home';
    case Outdoor = 'outdoor';
}
