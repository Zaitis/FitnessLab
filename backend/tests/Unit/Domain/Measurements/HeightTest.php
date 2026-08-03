<?php

use App\Domain\Measurements\ValueObjects\Height;

it('accepts a plausible height', function () {
    expect((new Height(175.0))->centimeters)->toBe(175.0);
});

it('rejects an implausible height', function (float $centimeters) {
    new Height($centimeters);
})->with([
    'below the minimum' => [29.9],
    'above the maximum' => [250.1],
    'negative' => [-5.0],
])->throws(InvalidArgumentException::class);
