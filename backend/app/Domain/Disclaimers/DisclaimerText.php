<?php

namespace App\Domain\Disclaimers;

final readonly class DisclaimerText
{
    public function __construct(
        public string $short,
        public string $standard,
        public string $extended,
    ) {}

    /**
     * @return array{short: string, standard: string, extended: string}
     */
    public function toArray(): array
    {
        return [
            'short' => $this->short,
            'standard' => $this->standard,
            'extended' => $this->extended,
        ];
    }
}
