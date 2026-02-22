<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Models\ClientDiscount;
use App\Models\PaymentTemplate;
use App\Models\ProductFamily;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->search($request->input('search'))
            ->when($request->input('type'), function ($q, $type) {
                if ($type === 'customer') return $q->customers();
                if ($type === 'supplier') return $q->suppliers();
                return $q->where('type', $type);
            })
            ->when(
                $request->input('sort'),
                fn ($q) => $q->orderBy($request->input('sort'), $request->input('dir', 'asc')),
                fn ($q) => $q->orderBy('legal_name', 'asc')
            )
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'type', 'sort', 'dir']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create', [
            'paymentTemplates' => PaymentTemplate::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ClientRequest $request)
    {
        Client::create($request->validated());

        return redirect()->route('clients.index')
            ->with('success', __('clients.flash_created'));
    }

    public function edit(Client $client)
    {
        $client->load(['discounts.productFamily:id,name']);

        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'paymentTemplates' => PaymentTemplate::orderBy('name')->get(['id', 'name']),
            'productFamilies' => ProductFamily::orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()->route('clients.index')
            ->with('success', __('clients.flash_updated'));
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', __('clients.flash_deleted'));
    }

    public function storeDiscount(Request $request, Client $client)
    {
        $validated = $request->validate([
            'discount_type' => 'required|in:general,agreement,type,family',
            'discount_percent' => 'required|numeric|min:0.01|max:100',
            'product_type' => 'nullable|required_if:discount_type,type|in:product,service',
            'product_family_id' => 'nullable|required_if:discount_type,family|exists:product_families,id',
            'min_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'notes' => 'nullable|string|max:500',
        ]);

        $client->discounts()->create($validated);

        return back()->with('success', __('clients.flash_discount_added'));
    }

    public function updateDiscount(Request $request, Client $client, ClientDiscount $discount)
    {
        if ($discount->client_id !== $client->id) {
            abort(404);
        }

        $validated = $request->validate([
            'discount_type' => 'required|in:general,agreement,type,family',
            'discount_percent' => 'required|numeric|min:0.01|max:100',
            'product_type' => 'nullable|required_if:discount_type,type|in:product,service',
            'product_family_id' => 'nullable|required_if:discount_type,family|exists:product_families,id',
            'min_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'notes' => 'nullable|string|max:500',
        ]);

        $discount->update($validated);

        return back()->with('success', __('clients.flash_discount_updated'));
    }

    public function destroyDiscount(Client $client, ClientDiscount $discount)
    {
        if ($discount->client_id !== $client->id) {
            abort(404);
        }

        $discount->delete();

        return back()->with('success', __('clients.flash_discount_deleted'));
    }
}
