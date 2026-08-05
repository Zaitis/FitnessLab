<?php

namespace App\Domain\Documents\ValueObjects;

final readonly class PlanExportSection
{
    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $heading,
        public array $lines,
    ) {}
}
