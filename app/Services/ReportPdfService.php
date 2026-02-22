<?php

namespace App\Services;

use App\Models\CompanyProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Symfony\Component\HttpFoundation\Response;

class ReportPdfService
{
    public function generate(string $view, array $data, string $orientation = 'portrait'): DomPdfInstance
    {
        $data['company'] = $data['company'] ?? CompanyProfile::first();
        $data['generatedAt'] = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', $orientation);
        $pdf->setOption('defaultFont', 'DejaVu Sans');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    public function download(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        return $this->generate($view, $data, $orientation)->download($filename);
    }

    public function stream(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        return $this->generate($view, $data, $orientation)->stream($filename);
    }
}
