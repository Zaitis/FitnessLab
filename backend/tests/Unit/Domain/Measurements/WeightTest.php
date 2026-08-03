<?php

use App\Domain\Measurements\ValueObjects\Weight;

it('accepts a plausible weight', function () {
    expect((new Weight(70.5))->kilograms)->toBe(70.5);
});

it('rejects an implausible weight', function (float $kilograms) {
    new Weight($kilograms);
})->with([
    'below the minimum' => [0.9],
    'above the maximum' => [500.1],
    'negative' => [-10.0],
])->throws(InvalidArgumentException::class);
