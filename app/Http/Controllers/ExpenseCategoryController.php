<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Expenses/Categories', [
            'categories' => ExpenseCategory::with('children')
                ->roots()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'allCategories' => ExpenseCategory::orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'parent_id' => ['nullable', 'exists:expense_categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        ExpenseCategory::create($validated);

        return back()->with('success', __('expenses.flash_category_created'));
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20'],
            'parent_id' => ['nullable', 'exists:expense_categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Prevent self-reference
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return back()->with('error', __('expenses.error_category_self_parent'));
        }

        $category->update($validated);

        return back()->with('success', __('expenses.flash_category_updated'));
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->exists()) {
            return back()->with('error', __('expenses.error_category_has_expenses'));
        }

        if ($category->children()->exists()) {
            return back()->with('error', __('expenses.error_category_has_children'));
        }

        $category->delete();

        return back()->with('success', __('expenses.flash_category_deleted'));
    }
}
