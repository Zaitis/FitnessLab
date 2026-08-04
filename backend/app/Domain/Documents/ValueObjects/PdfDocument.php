<?php

namespace App\Domain\Documents\ValueObjects;

final readonly class PdfDocument
{
    public function __construct(
        public string $contents,
        public string $filename,
    ) {}
}
