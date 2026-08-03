<?php

use App\Domain\Measurements\ValueObjects\Bmi;
use App\Domain\Measurements\ValueObjects\BmiCategory;
use App\Domain\Measurements\ValueObjects\Height;
use App\Domain\Measurements\ValueObjects\Weight;

it('calculates the bmi value and category from measurements', function () {
    $bmi = Bmi::fromMeasurements(new Weight(70), new Height(175));

    expect($bmi->value)->toBe(22.9)
        ->and($bmi->category)->toBe(BmiCategory::Normal);
});
