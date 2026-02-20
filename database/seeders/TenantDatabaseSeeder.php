<?php

namespace Database\Seeders;

use App\Models\DocumentSeries;
use App\Models\ExpenseCategory;
use App\Models\PdfTemplate;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        $series = [
            ['document_type' => 'invoice', 'prefix' => "FACT-{$year}-"],
            ['document_type' => 'quote', 'prefix' => "PRES-{$year}-"],
            ['document_type' => 'delivery_note', 'prefix' => "ALB-{$year}-"],
            ['document_type' => 'rectificative', 'prefix' => "RECT-{$year}-"],
            ['document_type' => 'proforma', 'prefix' => "PROF-{$year}-"],
            ['document_type' => 'receipt', 'prefix' => "REC-{$year}-"],
            ['document_type' => 'purchase_invoice', 'prefix' => "COMP-{$year}-"],
        ];

        foreach ($series as $s) {
            DocumentSeries::create([
                ...$s,
                'next_number' => 1,
                'padding' => 5,
                'fiscal_year' => $year,
                'is_default' => true,
            ]);
        }

        // Expense categories
        $categories = [
            ['code' => 'ALQ', 'name' => 'Alquiler de local', 'sort_order' => 1],
            ['code' => 'SUM', 'name' => 'Suministros (luz, agua, gas)', 'sort_order' => 2],
            ['code' => 'TEL', 'name' => 'Telecomunicaciones e internet', 'sort_order' => 3],
            ['code' => 'MAT', 'name' => 'Material de oficina', 'sort_order' => 4],
            ['code' => 'EQU', 'name' => 'Equipos informáticos', 'sort_order' => 5],
            ['code' => 'SOF', 'name' => 'Software y licencias', 'sort_order' => 6],
            ['code' => 'VEH', 'name' => 'Vehículo (combustible, mantenimiento)', 'sort_order' => 7],
            ['code' => 'VIA', 'name' => 'Viajes y desplazamientos', 'sort_order' => 8],
            ['code' => 'DIE', 'name' => 'Dietas y comidas', 'sort_order' => 9],
            ['code' => 'SEG', 'name' => 'Seguros', 'sort_order' => 10],
            ['code' => 'ASE', 'name' => 'Asesoría y gestoría', 'sort_order' => 11],
            ['code' => 'PUB', 'name' => 'Publicidad y marketing', 'sort_order' => 12],
            ['code' => 'FOR', 'name' => 'Formación', 'sort_order' => 13],
            ['code' => 'BAN', 'name' => 'Gastos bancarios', 'sort_order' => 14],
            ['code' => 'IMP', 'name' => 'Impuestos y tasas', 'sort_order' => 15],
            ['code' => 'OTR', 'name' => 'Otros gastos', 'sort_order' => 99],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::create($cat);
        }

        // PDF templates
        PdfTemplate::create([
            'name' => 'Clásica',
            'blade_view' => 'pdf.documents.default',
            'is_default' => true,
            'settings' => [
                'primary_color' => '#1f2937',
                'accent_color' => '#4f46e5',
                'font_family' => 'DejaVu Sans',
                'show_logo' => true,
                'show_qr' => true,
            ],
        ]);

        PdfTemplate::create([
            'name' => 'Moderna',
            'blade_view' => 'pdf.documents.modern',
            'is_default' => false,
            'settings' => [
                'primary_color' => '#4f46e5',
                'accent_color' => '#6366f1',
                'font_family' => 'DejaVu Sans',
                'show_logo' => true,
                'show_qr' => true,
            ],
        ]);
    }
}
