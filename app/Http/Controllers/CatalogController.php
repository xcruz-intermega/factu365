<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Services\ReportPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CatalogController extends Controller
{
    // ─── Internal Catalog (auth) ───

    public function index(Request $request)
    {
        $products = Product::query()
            ->with('family:id,name')
            ->search($request->input('search'))
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('family'), fn ($q, $familyId) => $q->where('product_family_id', $familyId))
            ->orderBy('name', 'asc')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'filters' => $request->only(['search', 'type', 'family']),
            'families' => ProductFamily::orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function pdf(Request $request, ReportPdfService $reportPdfService)
    {
        $showPrices = $request->boolean('prices', true);
        $showImages = $request->boolean('images', true);

        $families = ProductFamily::with(['products' => fn ($q) => $q->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorized = Product::whereNull('product_family_id')->orderBy('name')->get();
        $company = CompanyProfile::first();

        return $reportPdfService->download(
            'pdf.reports.catalog',
            [
                'families' => $families,
                'uncategorized' => $uncategorized,
                'showPrices' => $showPrices,
                'showImages' => $showImages,
                'company' => $company,
            ],
            __('products.catalog_pdf_title') . '_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    // ─── Public Catalog (no auth) ───

    public function publicIndex(Request $request)
    {
        $company = CompanyProfile::first();

        if (! $company || ! $company->catalog_enabled) {
            abort(404);
        }

        $products = Product::query()
            ->with('family:id,name')
            ->search($request->input('search'))
            ->when($request->input('family'), fn ($q, $familyId) => $q->where('product_family_id', $familyId))
            ->orderBy('name', 'asc')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Catalog/Public', [
            'products' => $products,
            'filters' => $request->only(['search', 'family']),
            'families' => ProductFamily::orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']),
            'company' => [
                'legal_name' => $company->legal_name,
                'trade_name' => $company->trade_name,
                'phone' => $company->phone,
                'email' => $company->email,
                'website' => $company->website,
            ],
        ]);
    }

    public function publicPdf(Request $request, ReportPdfService $reportPdfService)
    {
        $company = CompanyProfile::first();

        if (! $company || ! $company->catalog_enabled) {
            abort(404);
        }

        $showPrices = $request->boolean('prices', true);
        $showImages = $request->boolean('images', true);

        $families = ProductFamily::with(['products' => fn ($q) => $q->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorized = Product::whereNull('product_family_id')->orderBy('name')->get();

        return $reportPdfService->download(
            'pdf.reports.catalog',
            [
                'families' => $families,
                'uncategorized' => $uncategorized,
                'showPrices' => $showPrices,
                'showImages' => $showImages,
                'company' => $company,
            ],
            __('products.catalog_pdf_title') . '_' . now()->format('Y-m-d') . '.pdf',
        );
    }

    public function productImage(Product $product)
    {
        $company = CompanyProfile::first();

        if (! $company || ! $company->catalog_enabled) {
            abort(404);
        }

        if (! $product->image_path || ! Storage::disk('local')->exists($product->image_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($product->image_path));
    }
}
