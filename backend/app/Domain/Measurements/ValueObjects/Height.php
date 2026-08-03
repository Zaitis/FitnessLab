<?php

namespace App\Domain\Measurements\ValueObjects;

use InvalidArgumentException;

final readonly class Height
{
    private const float MIN_CM = 30.0;

    private const float MAX_CM = 250.0;

    public function __construct(public float $centimeters)
    {
        if ($centimeters < self::MIN_CM || $centimeters > self::MAX_CM) {
            throw new InvalidArgumentException(sprintf(
                'Height must be between %.1f and %.1f cm, got %.1f.',
                self::MIN_CM,
                self::MAX_CM,
                $centimeters,
            ));
        }
    }
}
