<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Services\EInvoice\FacturaEBuilderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use ZipArchive;

class FacturaEExportController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $documents = $query->paginate(50)->withQueryString();

        $totalCount = $this->buildQuery($request)->count();

        $statuses = [
            Document::STATUS_FINALIZED,
            Document::STATUS_SENT,
            Document::STATUS_PARTIAL,
            Document::STATUS_PAID,
            Document::STATUS_OVERDUE,
            Document::STATUS_CANCELLED,
        ];

        $clients = Client::query()
            ->where('type', 'customer')
            ->orderBy('legal_name')
            ->get(['id', 'legal_name', 'trade_name']);

        return Inertia::render('Documents/ExportFacturae', [
            'documents' => $documents,
            'filters' => [
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
                'status' => $request->input('status', ''),
                'client_id' => $request->input('client_id', ''),
            ],
            'statuses' => $statuses,
            'clients' => $clients,
            'totalCount' => $totalCount,
        ]);
    }

    public function download(Request $request)
    {
        $query = $this->buildQuery($request);
        $count = $query->count();

        if ($count === 0) {
            return back()->with('error', __('documents.export_error_empty'));
        }

        if ($count > 500) {
            return back()->with('error', __('documents.export_error_limit'));
        }

        $documents = $query->get();
        $builder = new FacturaEBuilderService();

        $zipPath = sys_get_temp_dir() . '/FacturaE_export_' . date('Y-m-d') . '_' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($documents as $document) {
            $xml = $builder->generate($document);
            $filename = 'FacturaE_' . str_replace('/', '-', $document->number) . '.xml';
            $zip->addFromString($filename, $xml);
        }

        $zip->close();

        $downloadName = 'FacturaE_export_' . date('Y-m-d') . '.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    private function buildQuery(Request $request)
    {
        return Document::query()
            ->whereIn('document_type', [Document::TYPE_INVOICE, Document::TYPE_RECTIFICATIVE])
            ->where('direction', 'issued')
            ->where('status', '!=', Document::STATUS_DRAFT)
            ->with(['client', 'lines', 'series', 'correctedDocument'])
            ->when($request->input('date_from'), fn ($q, $d) => $q->where('issue_date', '>=', $d))
            ->when($request->input('date_to'), fn ($q, $d) => $q->where('issue_date', '<=', $d))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('client_id'), fn ($q, $c) => $q->where('client_id', $c))
            ->orderBy('issue_date', 'desc')
            ->orderBy('number', 'desc');
    }
}
