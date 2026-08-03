<?php

namespace App\Domain\Measurements\ValueObjects;

use InvalidArgumentException;

final readonly class Weight
{
    private const float MIN_KG = 1.0;

    private const float MAX_KG = 500.0;

    public function __construct(public float $kilograms)
    {
        if ($kilograms < self::MIN_KG || $kilograms > self::MAX_KG) {
            throw new InvalidArgumentException(sprintf(
                'Weight must be between %.1f and %.1f kg, got %.1f.',
                self::MIN_KG,
                self::MAX_KG,
                $kilograms,
            ));
        }
    }
}
