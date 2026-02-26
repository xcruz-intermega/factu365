<?php

namespace App\Services;

use App\Models\PdfTemplate;
use Illuminate\Support\Facades\View;

class PdfLayoutRenderer
{
    public function render(array $layoutJson, array $data): string
    {
        $blocks = collect($layoutJson['blocks'] ?? [])
            ->filter(fn ($block) => $block['enabled'] ?? false)
            ->values()
            ->toArray();

        $global = $layoutJson['global'] ?? PdfTemplate::getDefaultLayout()['global'];

        return View::make('pdf.documents.dynamic', array_merge($data, [
            'blocks' => $blocks,
            'global' => $global,
        ]))->render();
    }
}
