<?php

namespace App\Http\Controllers;

use App\Models\ProductFamily;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductFamilyController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/ProductFamilies', [
            'families' => ProductFamily::with('children')
                ->roots()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'allFamilies' => ProductFamily::orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'parent_id' => ['nullable', 'exists:product_families,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        ProductFamily::create($validated);

        return back()->with('success', 'Familia creada correctamente.');
    }

    public function update(Request $request, ProductFamily $family)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'parent_id' => ['nullable', 'exists:product_families,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Prevent self-reference
        if (isset($validated['parent_id']) && $validated['parent_id'] == $family->id) {
            return back()->with('error', 'Una familia no puede ser padre de sí misma.');
        }

        $family->update($validated);

        return back()->with('success', 'Familia actualizada.');
    }

    public function destroy(ProductFamily $family)
    {
        if ($family->products()->exists()) {
            return back()->with('error', 'No se puede eliminar una familia con productos asociados.');
        }

        if ($family->children()->exists()) {
            return back()->with('error', 'No se puede eliminar una familia con subfamilias.');
        }

        $family->delete();

        return back()->with('success', 'Familia eliminada.');
    }
}
