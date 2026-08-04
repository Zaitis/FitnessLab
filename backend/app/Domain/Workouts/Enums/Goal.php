<?php

namespace App\Domain\Workouts\Enums;

enum Goal: string
{
    case FatLoss = 'fat_loss';
    case MuscleGain = 'muscle_gain';
    case Maintenance = 'maintenance';
}
