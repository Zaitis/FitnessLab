<?php

namespace App\Infrastructure\Pdf;

use App\Domain\Documents\Contracts\PdfExporterInterface;
use App\Domain\Documents\ValueObjects\PdfDocument;
use App\Domain\Documents\ValueObjects\PlanExportData;
use Barryvdh\DomPDF\PDF;

final class DompdfPlanExporter implements PdfExporterInterface
{
    public function __construct(private readonly PDF $pdf) {}

    public function export(PlanExportData $data): PdfDocument
    {
        $pdf = $this->pdf->loadView('pdf.plan', ['data' => $data])->setPaper('a4');

        return new PdfDocument(
            contents: $pdf->output(),
            filename: $data->filename,
        );
    }
}
