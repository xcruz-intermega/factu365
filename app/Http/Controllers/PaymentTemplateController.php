<?php

namespace App\Http\Controllers;

use App\Models\PaymentTemplate;
use App\Models\PaymentTemplateLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/PaymentTemplates', [
            'templates' => PaymentTemplate::with('lines')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_default' => ['boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.days_from_issue' => ['required', 'integer', 'min:0'],
            'lines.*.percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        DB::transaction(function () use ($validated) {
            if ($validated['is_default'] ?? false) {
                PaymentTemplate::where('is_default', true)->update(['is_default' => false]);
            }

            $template = PaymentTemplate::create([
                'name' => $validated['name'],
                'is_default' => $validated['is_default'] ?? false,
            ]);

            foreach ($validated['lines'] as $index => $line) {
                $template->lines()->create([
                    'days_from_issue' => $line['days_from_issue'],
                    'percentage' => $line['percentage'],
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return back()->with('success', 'Plantilla de vencimiento creada.');
    }

    public function update(Request $request, PaymentTemplate $template)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_default' => ['boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.days_from_issue' => ['required', 'integer', 'min:0'],
            'lines.*.percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        DB::transaction(function () use ($template, $validated) {
            if ($validated['is_default'] ?? false) {
                PaymentTemplate::where('id', '!=', $template->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $template->update([
                'name' => $validated['name'],
                'is_default' => $validated['is_default'] ?? false,
            ]);

            // Replace lines
            $template->lines()->delete();
            foreach ($validated['lines'] as $index => $line) {
                $template->lines()->create([
                    'days_from_issue' => $line['days_from_issue'],
                    'percentage' => $line['percentage'],
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return back()->with('success', 'Plantilla actualizada.');
    }

    public function destroy(PaymentTemplate $template)
    {
        $template->lines()->delete();
        $template->delete();

        return back()->with('success', 'Plantilla eliminada.');
    }

    /**
     * Seed default templates if table is empty.
     */
    public static function seedDefaults(): void
    {
        if (PaymentTemplate::count() > 0) {
            return;
        }

        $templates = [
            ['name' => 'Contado', 'is_default' => true, 'lines' => [
                ['days_from_issue' => 0, 'percentage' => 100],
            ]],
            ['name' => '30 días', 'is_default' => false, 'lines' => [
                ['days_from_issue' => 30, 'percentage' => 100],
            ]],
            ['name' => '30-60 días', 'is_default' => false, 'lines' => [
                ['days_from_issue' => 30, 'percentage' => 50],
                ['days_from_issue' => 60, 'percentage' => 50],
            ]],
            ['name' => '30-60-90 días', 'is_default' => false, 'lines' => [
                ['days_from_issue' => 30, 'percentage' => 33.33],
                ['days_from_issue' => 60, 'percentage' => 33.33],
                ['days_from_issue' => 90, 'percentage' => 33.34],
            ]],
        ];

        foreach ($templates as $tpl) {
            $template = PaymentTemplate::create([
                'name' => $tpl['name'],
                'is_default' => $tpl['is_default'],
            ]);

            foreach ($tpl['lines'] as $index => $line) {
                $template->lines()->create([
                    'days_from_issue' => $line['days_from_issue'],
                    'percentage' => $line['percentage'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
