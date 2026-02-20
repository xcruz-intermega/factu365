<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\PdfTemplate;
use App\Services\VeriFactu\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfGeneratorService
{
    public function __construct(
        private QrCodeService $qrCodeService,
    ) {}

    public function generate(Document $document, ?PdfTemplate $template = null): \Barryvdh\DomPDF\PDF
    {
        $template ??= PdfTemplate::getDefault();
        $company = CompanyProfile::first();

        $document->loadMissing(['client', 'lines.product', 'series', 'correctedDocument']);

        $vatBreakdown = $this->buildVatBreakdown($document);
        $qrDataUri = $this->generateQrDataUri($document, $company);

        $data = [
            'document' => $document,
            'company' => $company,
            'lines' => $document->lines,
            'vatBreakdown' => $vatBreakdown,
            'qrDataUri' => $qrDataUri,
            'template' => $template,
            'settings' => $template?->settings ?? [],
            'typeLabel' => Document::documentTypeLabel($document->document_type),
            'statusLabel' => Document::statusLabel($document->status),
        ];

        $view = $template?->blade_view ?? 'pdf.documents.default';

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4');
        $pdf->setOption('defaultFont', $template?->getSetting('font_family', 'DejaVu Sans'));
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    public function download(Document $document, ?PdfTemplate $template = null): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($document, $template);

        return $pdf->download($this->filename($document));
    }

    public function stream(Document $document, ?PdfTemplate $template = null): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($document, $template);

        return $pdf->stream($this->filename($document));
    }

    public function content(Document $document, ?PdfTemplate $template = null): string
    {
        $pdf = $this->generate($document, $template);

        return $pdf->output();
    }

    private function filename(Document $document): string
    {
        $type = str_replace(' ', '_', Document::documentTypeLabel($document->document_type));
        $number = $document->number ? str_replace(['/', '\\'], '-', $document->number) : 'borrador_' . $document->id;

        return "{$type}_{$number}.pdf";
    }

    private function buildVatBreakdown(Document $document): Collection
    {
        return $document->lines
            ->groupBy('vat_rate')
            ->map(function ($lines, $rate) {
                return [
                    'vat_rate' => $rate,
                    'base' => $lines->sum(fn ($l) => (float) $l->line_subtotal),
                    'vat' => $lines->sum(fn ($l) => (float) $l->vat_amount),
                    'surcharge' => $lines->sum(fn ($l) => (float) $l->surcharge_amount),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function generateQrDataUri(Document $document, ?CompanyProfile $company): ?string
    {
        if (! $company?->nif || ! $document->number) {
            return null;
        }

        if (! $document->isInvoice() && ! $document->isRectificative()) {
            return null;
        }

        try {
            return $this->qrCodeService->generateQrCodeDataUri($document, $company->nif);
        } catch (\Throwable) {
            return null;
        }
    }
}
