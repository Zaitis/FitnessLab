<?php

namespace App\Domain\Documents\ValueObjects;

use DateTimeImmutable;

/**
 * Everything a PdfExporterInterface implementation needs to render a plan
 * export — plan-type-agnostic on purpose (docs/DESIGN-PATTERNS.md §6): the
 * exporter renders sections and lines without knowing whether they came
 * from a workout or a nutrition plan, and ExportPlanToPdfAction never
 * touches HTML, CSS, or watermark opacity.
 */
final readonly class PlanExportData
{
    /**
     * @param  list<PlanExportSection>  $sections
     */
    public function __construct(
        public string $filename,
        public string $title,
        public string $summary,
        public array $sections,
        public string $disclaimer,
        public string $locale,
        public DateTimeImmutable $generatedAt,
    ) {}
}
