<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Clients
        Client::where('legal_name', 'like', "%{$q}%")
            ->orWhere('trade_name', 'like', "%{$q}%")
            ->orWhere('nif', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'legal_name', 'nif'])
            ->each(function ($c) use (&$results) {
                $results[] = [
                    'type' => 'client',
                    'label' => $c->legal_name,
                    'detail' => $c->nif,
                    'url' => route('clients.edit', $c->id),
                ];
            });

        // Products
        Product::where('name', 'like', "%{$q}%")
            ->orWhere('reference', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name', 'reference'])
            ->each(function ($p) use (&$results) {
                $results[] = [
                    'type' => 'product',
                    'label' => $p->name,
                    'detail' => $p->reference,
                    'url' => route('products.edit', $p->id),
                ];
            });

        // Documents (invoices, quotes, etc.)
        Document::where('number', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'document_type', 'direction', 'number', 'status'])
            ->each(function ($d) use (&$results) {
                $typeLabels = [
                    'invoice' => 'Factura',
                    'quote' => 'Presupuesto',
                    'delivery_note' => 'Albarán',
                    'rectificative' => 'Rectificativa',
                    'purchase_invoice' => 'Fact. recibida',
                ];
                $results[] = [
                    'type' => 'document',
                    'label' => ($typeLabels[$d->document_type] ?? $d->document_type) . ' ' . $d->number,
                    'detail' => $d->status,
                    'url' => route('documents.edit', ['type' => $d->document_type, 'document' => $d->id]),
                ];
            });

        // Expenses
        Expense::where('concept', 'like', "%{$q}%")
            ->orWhere('invoice_number', 'like', "%{$q}%")
            ->orWhere('supplier_name', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'concept', 'total'])
            ->each(function ($e) use (&$results) {
                $results[] = [
                    'type' => 'expense',
                    'label' => $e->concept ?: 'Gasto #' . $e->id,
                    'detail' => number_format((float) $e->total, 2, ',', '.') . ' €',
                    'url' => route('expenses.edit', $e->id),
                ];
            });

        return response()->json(['results' => array_slice($results, 0, 15)]);
    }
}
