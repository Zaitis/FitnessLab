<?php

namespace App\Application\Measurements\Actions;

use App\Domain\Measurements\ValueObjects\Bmi;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;

final class CalculateBmiAction
{
    public function execute(Weight $weight, Height $height): Bmi
    {
        return Bmi::fromMeasurements($weight, $height);
    }
}
