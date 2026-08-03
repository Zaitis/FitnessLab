<?php

namespace App\Domain\Disclaimers;

final readonly class DisclaimerText
{
    public function __construct(
        public string $short,
        public string $standard,
        public string $extended,
    ) {}
}
