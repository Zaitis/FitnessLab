<?php

namespace App\Domain\Documents\Contracts;

use App\Domain\Documents\ValueObjects\PdfDocument;
use App\Domain\Documents\ValueObjects\PlanExportData;

/**
 * Owned by the domain, implemented by infrastructure
 * (docs/DESIGN-PATTERNS.md §6) — the Action expresses *what* is exported and
 * under what authorization; how a specific library rasterises HTML is an
 * infrastructure concern.
 */
interface PdfExporterInterface
{
    public function export(PlanExportData $data): PdfDocument;
}
