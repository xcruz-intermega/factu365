<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\DocumentDueDate;
use App\Models\DocumentLine;
use App\Models\DocumentSeries;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentTemplate;
use App\Models\PaymentTemplateLine;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TreasuryEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Typography\FontFactory;

class DemoDataSeeder extends Seeder
{
    private array $provinces = [
        'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza', 'Málaga',
        'Murcia', 'Palma de Mallorca', 'Las Palmas', 'Bilbao', 'Alicante',
        'Córdoba', 'Valladolid', 'Vigo', 'Gijón', 'Granada', 'A Coruña',
        'Vitoria-Gasteiz', 'Santa Cruz de Tenerife', 'Pamplona', 'Salamanca',
        'Burgos', 'Albacete', 'San Sebastián', 'Santander', 'Toledo', 'Logroño',
    ];

    private array $streets = [
        'Calle Mayor', 'Calle del Comercio', 'Avenida de la Constitución', 'Paseo de la Castellana',
        'Calle Gran Vía', 'Calle de Alcalá', 'Avenida de América', 'Calle Serrano',
        'Calle de Velázquez', 'Paseo de Gracia', 'Rambla de Catalunya', 'Calle Balmes',
        'Avenida Diagonal', 'Calle de la Paz', 'Calle Colón', 'Calle San Vicente',
        'Avenida de Andalucía', 'Calle Real', 'Calle Nueva', 'Paseo del Prado',
        'Calle de la Libertad', 'Avenida del Puerto', 'Calle de la Industria',
        'Polígono Industrial Los Olivos', 'Polígono Industrial El Fuerte',
    ];

    private array $customerNames = [
        'Tecnologías Avanzadas del Sur SL', 'Construcciones Hermanos López SA', 'Distribuciones García Martín SL',
        'Consultores Asociados del Norte SL', 'Inmobiliaria Costa Brava SA', 'Transportes Rápidos Iberia SL',
        'Electrónica Industrial Vega SL', 'Farmacia Central García SL', 'Asesoría Fiscal Pérez y Asociados SL',
        'Clínica Dental Sonrisa SL', 'Restaurante El Mirador SL', 'Hotel Playa Dorada SA',
        'Imprenta Digital Express SL', 'Carpintería Artesanal Ruiz SL', 'Fontanería y Calefacción Martín SL',
        'Agencia de Viajes Exploramundo SL', 'Academia de Idiomas Babel SL', 'Taller Mecánico Autosur SL',
        'Peluquería Estilo Moderno SL', 'Gimnasio FitVida SL', 'Panadería La Espiga de Oro SL',
        'Floristería Jardín del Edén SL', 'Librería El Saber SL', 'Óptica Visión Clara SL',
        'Ferretería Industrial del Centro SL', 'Joyería Diamante Azul SL', 'Zapatería El Paso Firme SL',
        'Papelería Oficinas Plus SL', 'Veterinaria Animal Sano SL', 'Autoescuela Vía Segura SL',
        'Viveros y Jardines del Mediterráneo SL', 'Centro Estético Belleza Natural SL',
        'Carnicería Premium Don Jamón SL', 'Pescadería Mariscos del Cantábrico SL',
        'Bodega Vinos de la Tierra SL', 'Pastelería Dulces Momentos SL',
        'Lavandería Industrial Clean SL', 'Fotografía y Vídeo Enfoque SL',
        'Decoración de Interiores Hogar SL', 'Cerrajería 24 Horas Seguridad SL',
        'Gestoría Administrativa Trámites SL', 'Catering y Eventos Celebra SL',
        'Limpieza Industrial Brillo SL', 'Mudanzas y Transportes Express SL',
        'Informática y Redes DataTech SL', 'Publicidad y Marketing Creativo SL',
        'Arquitectura y Diseño Espacios SL', 'Fontanería de Urgencia Aqua SL',
        'Reformas Integrales del Hogar SL', 'Seguros Protección Total SA',
        'Alquiler de Maquinaria Industrial SL', 'Suministros Eléctricos del Norte SL',
        'Materiales de Construcción Cemex SL', 'Servicios de Seguridad Vigila SL',
        'Clínica Veterinaria Mascotas SL', 'Estudio de Arquitectura Forma SL',
        'Agencia Inmobiliaria Hogar Feliz SL', 'Centro de Formación Avanza SL',
        'Laboratorio Análisis Clínicos SL', 'Empresa de Jardinería Verde SL',
        'Tintorería y Arreglos Moda SL', 'Centro de Fisioterapia Salud SL',
        'Tienda de Informática ByteShop SL', 'Cristalería y Vidrios del Sur SL',
        'Empresa de Pintura Colores SL', 'Muebles a Medida Diseño SL',
        'Instalaciones Eléctricas Rayo SL', 'Centro Médico Bienestar SL',
        'Droguería y Perfumería Aroma SL', 'Guardería Infantil Sonrisas SL',
    ];

    private array $supplierNames = [
        'Suministros Industriales Ibérica SA', 'Mayorista Tecnología ProTech SL',
        'Distribuciones Alimentarias del Centro SA', 'Papeles y Embalajes Nacionales SA',
        'Suministros de Oficina OfiMax SL', 'Telefonía y Comunicaciones Red SA',
        'Energía Solar Renovables SL', 'Seguros Comerciales Mapfre SA',
        'Material Eléctrico Schneider SL', 'Suministros Médicos Hospitalar SL',
        'Productos Químicos Quimiberica SA', 'Renting de Vehículos AutoFlex SA',
        'Servicios Cloud Amazon España SL', 'Software de Gestión ERP Solutions SL',
        'Alquiler de Oficinas WorkSpace SA', 'Combustibles y Gasolinas Repsol SA',
        'Agua y Saneamiento Aqualia SA', 'Electricidad Endesa Distribución SA',
        'Gas Natural Fenosa SA', 'Vodafone España Telecomunicaciones SA',
        'Movistar Empresas Telefónica SA', 'Mutua de Accidentes Fraternidad SA',
        'Limpieza y Mantenimiento Clece SA', 'Seguridad Prosegur España SA',
        'Material de Oficina Staples SL', 'Hosting y Dominios SiteGround SL',
        'Publicidad Online Google Spain SL', 'Asesoría Laboral Sagardoy SL',
        'Gestión de Nóminas ADP Spain SL', 'Correduría de Seguros Willis SL',
    ];

    private array $productNames = [
        // Productos
        ['name' => 'Ordenador portátil HP ProBook 450', 'type' => 'product', 'price' => 899.00, 'vat' => 21, 'ref' => 'PROD-001'],
        ['name' => 'Monitor LG UltraWide 34"', 'type' => 'product', 'price' => 449.00, 'vat' => 21, 'ref' => 'PROD-002'],
        ['name' => 'Teclado mecánico Logitech MX', 'type' => 'product', 'price' => 129.00, 'vat' => 21, 'ref' => 'PROD-003'],
        ['name' => 'Ratón inalámbrico Logitech MX Master', 'type' => 'product', 'price' => 89.00, 'vat' => 21, 'ref' => 'PROD-004'],
        ['name' => 'Impresora multifunción HP LaserJet', 'type' => 'product', 'price' => 349.00, 'vat' => 21, 'ref' => 'PROD-005'],
        ['name' => 'Router WiFi 6 ASUS', 'type' => 'product', 'price' => 189.00, 'vat' => 21, 'ref' => 'PROD-006'],
        ['name' => 'Disco SSD Samsung 1TB', 'type' => 'product', 'price' => 95.00, 'vat' => 21, 'ref' => 'PROD-007'],
        ['name' => 'Webcam Logitech C920 HD Pro', 'type' => 'product', 'price' => 79.00, 'vat' => 21, 'ref' => 'PROD-008'],
        ['name' => 'Auriculares Sony WH-1000XM5', 'type' => 'product', 'price' => 329.00, 'vat' => 21, 'ref' => 'PROD-009'],
        ['name' => 'Tablet Samsung Galaxy Tab S9', 'type' => 'product', 'price' => 649.00, 'vat' => 21, 'ref' => 'PROD-010'],
        ['name' => 'Silla ergonómica de oficina', 'type' => 'product', 'price' => 289.00, 'vat' => 21, 'ref' => 'PROD-011'],
        ['name' => 'Mesa de escritorio regulable', 'type' => 'product', 'price' => 459.00, 'vat' => 21, 'ref' => 'PROD-012'],
        ['name' => 'Destructora de documentos Fellowes', 'type' => 'product', 'price' => 159.00, 'vat' => 21, 'ref' => 'PROD-013'],
        ['name' => 'Pack cartuchos tinta impresora', 'type' => 'product', 'price' => 45.00, 'vat' => 21, 'ref' => 'PROD-014'],
        ['name' => 'Cable HDMI 2.1 Premium 3m', 'type' => 'product', 'price' => 18.50, 'vat' => 21, 'ref' => 'PROD-015'],
        ['name' => 'Hub USB-C 7 en 1', 'type' => 'product', 'price' => 45.00, 'vat' => 21, 'ref' => 'PROD-016'],
        ['name' => 'Memoria RAM DDR5 32GB', 'type' => 'product', 'price' => 125.00, 'vat' => 21, 'ref' => 'PROD-017'],
        ['name' => 'Soporte monitor ajustable', 'type' => 'product', 'price' => 39.90, 'vat' => 21, 'ref' => 'PROD-018'],
        ['name' => 'Alfombrilla escritorio XXL', 'type' => 'product', 'price' => 24.90, 'vat' => 21, 'ref' => 'PROD-019'],
        ['name' => 'Lámpara LED escritorio', 'type' => 'product', 'price' => 49.90, 'vat' => 21, 'ref' => 'PROD-020'],
        ['name' => 'Caja 500 folios A4 80gr', 'type' => 'product', 'price' => 5.50, 'vat' => 21, 'ref' => 'PROD-021'],
        ['name' => 'Pack bolígrafos BIC Cristal x50', 'type' => 'product', 'price' => 12.90, 'vat' => 21, 'ref' => 'PROD-022'],
        ['name' => 'Archivador AZ folio lomo ancho', 'type' => 'product', 'price' => 3.50, 'vat' => 21, 'ref' => 'PROD-023'],
        ['name' => 'Grapadora metálica 24/6', 'type' => 'product', 'price' => 8.90, 'vat' => 21, 'ref' => 'PROD-024'],
        ['name' => 'Cinta adhesiva transparente x6', 'type' => 'product', 'price' => 4.20, 'vat' => 21, 'ref' => 'PROD-025'],
        ['name' => 'Libro contabilidad 100 hojas', 'type' => 'product', 'price' => 6.50, 'vat' => 4, 'ref' => 'PROD-026'],
        ['name' => 'Manual fiscal IRPF 2025', 'type' => 'product', 'price' => 35.00, 'vat' => 4, 'ref' => 'PROD-027'],
        ['name' => 'Pan integral artesano 500g', 'type' => 'product', 'price' => 2.80, 'vat' => 4, 'ref' => 'PROD-028'],
        ['name' => 'Leche entera brick 1L x6', 'type' => 'product', 'price' => 5.40, 'vat' => 4, 'ref' => 'PROD-029'],
        ['name' => 'Aceite oliva virgen extra 1L', 'type' => 'product', 'price' => 8.90, 'vat' => 4, 'ref' => 'PROD-030'],
        // Servicios
        ['name' => 'Desarrollo web a medida', 'type' => 'service', 'price' => 75.00, 'vat' => 21, 'ref' => 'SERV-001', 'unit' => 'hour'],
        ['name' => 'Consultoría empresarial', 'type' => 'service', 'price' => 120.00, 'vat' => 21, 'ref' => 'SERV-002', 'unit' => 'hour'],
        ['name' => 'Diseño gráfico y branding', 'type' => 'service', 'price' => 65.00, 'vat' => 21, 'ref' => 'SERV-003', 'unit' => 'hour'],
        ['name' => 'Gestión de redes sociales mensual', 'type' => 'service', 'price' => 450.00, 'vat' => 21, 'ref' => 'SERV-004'],
        ['name' => 'Mantenimiento informático mensual', 'type' => 'service', 'price' => 200.00, 'vat' => 21, 'ref' => 'SERV-005'],
        ['name' => 'Posicionamiento SEO mensual', 'type' => 'service', 'price' => 350.00, 'vat' => 21, 'ref' => 'SERV-006'],
        ['name' => 'Hosting web anual Premium', 'type' => 'service', 'price' => 180.00, 'vat' => 21, 'ref' => 'SERV-007'],
        ['name' => 'Certificado SSL anual', 'type' => 'service', 'price' => 49.00, 'vat' => 21, 'ref' => 'SERV-008'],
        ['name' => 'Auditoría de seguridad informática', 'type' => 'service', 'price' => 1500.00, 'vat' => 21, 'ref' => 'SERV-009'],
        ['name' => 'Formación presencial (jornada)', 'type' => 'service', 'price' => 600.00, 'vat' => 21, 'ref' => 'SERV-010', 'unit' => 'unit'],
        ['name' => 'Soporte técnico remoto', 'type' => 'service', 'price' => 45.00, 'vat' => 21, 'ref' => 'SERV-011', 'unit' => 'hour'],
        ['name' => 'Migración de datos y servidores', 'type' => 'service', 'price' => 2000.00, 'vat' => 21, 'ref' => 'SERV-012'],
        ['name' => 'Desarrollo app móvil', 'type' => 'service', 'price' => 85.00, 'vat' => 21, 'ref' => 'SERV-013', 'unit' => 'hour'],
        ['name' => 'Asesoría fiscal trimestral', 'type' => 'service', 'price' => 150.00, 'vat' => 21, 'ref' => 'SERV-014'],
        ['name' => 'Asesoría laboral mensual', 'type' => 'service', 'price' => 120.00, 'vat' => 21, 'ref' => 'SERV-015'],
        ['name' => 'Servicio de limpieza oficinas', 'type' => 'service', 'price' => 300.00, 'vat' => 21, 'ref' => 'SERV-016'],
        ['name' => 'Traducción técnica EN-ES', 'type' => 'service', 'price' => 0.08, 'vat' => 21, 'ref' => 'SERV-017', 'unit' => 'unit'],
        ['name' => 'Fotografía corporativa sesión', 'type' => 'service', 'price' => 350.00, 'vat' => 21, 'ref' => 'SERV-018'],
        ['name' => 'Servicio de mensajería urgente', 'type' => 'service', 'price' => 15.00, 'vat' => 21, 'ref' => 'SERV-019'],
        ['name' => 'Alquiler sala reuniones (hora)', 'type' => 'service', 'price' => 25.00, 'vat' => 21, 'ref' => 'SERV-020', 'unit' => 'hour'],
        // More products to reach ~100
        ['name' => 'Servidor Dell PowerEdge T150', 'type' => 'product', 'price' => 1899.00, 'vat' => 21, 'ref' => 'PROD-031'],
        ['name' => 'Switch Cisco 24 puertos PoE', 'type' => 'product', 'price' => 589.00, 'vat' => 21, 'ref' => 'PROD-032'],
        ['name' => 'SAI/UPS APC 1500VA', 'type' => 'product', 'price' => 349.00, 'vat' => 21, 'ref' => 'PROD-033'],
        ['name' => 'Proyector Epson Full HD', 'type' => 'product', 'price' => 599.00, 'vat' => 21, 'ref' => 'PROD-034'],
        ['name' => 'Pizarra interactiva 75"', 'type' => 'product', 'price' => 2499.00, 'vat' => 21, 'ref' => 'PROD-035'],
        ['name' => 'Smartphone Samsung Galaxy S24', 'type' => 'product', 'price' => 849.00, 'vat' => 21, 'ref' => 'PROD-036'],
        ['name' => 'Funda portátil 15.6" Premium', 'type' => 'product', 'price' => 29.90, 'vat' => 21, 'ref' => 'PROD-037'],
        ['name' => 'Cargador universal USB-C 65W', 'type' => 'product', 'price' => 39.90, 'vat' => 21, 'ref' => 'PROD-038'],
        ['name' => 'Micrófono USB podcast', 'type' => 'product', 'price' => 69.00, 'vat' => 21, 'ref' => 'PROD-039'],
        ['name' => 'Cámara videovigilancia IP', 'type' => 'product', 'price' => 89.00, 'vat' => 21, 'ref' => 'PROD-040'],
        ['name' => 'Disco duro externo 2TB', 'type' => 'product', 'price' => 79.00, 'vat' => 21, 'ref' => 'PROD-041'],
        ['name' => 'Pendrive USB 128GB', 'type' => 'product', 'price' => 15.90, 'vat' => 21, 'ref' => 'PROD-042'],
        ['name' => 'Tarjeta gráfica NVIDIA RTX 4060', 'type' => 'product', 'price' => 329.00, 'vat' => 21, 'ref' => 'PROD-043'],
        ['name' => 'Fuente alimentación 750W 80+ Gold', 'type' => 'product', 'price' => 99.00, 'vat' => 21, 'ref' => 'PROD-044'],
        ['name' => 'Ventilador torre refrigeración CPU', 'type' => 'product', 'price' => 34.90, 'vat' => 21, 'ref' => 'PROD-045'],
        ['name' => 'Licencia Microsoft 365 Business anual', 'type' => 'service', 'price' => 264.00, 'vat' => 21, 'ref' => 'SERV-021'],
        ['name' => 'Licencia antivirus empresarial anual', 'type' => 'service', 'price' => 89.00, 'vat' => 21, 'ref' => 'SERV-022'],
        ['name' => 'Backup en la nube 1TB mensual', 'type' => 'service', 'price' => 19.90, 'vat' => 21, 'ref' => 'SERV-023'],
        ['name' => 'Dominio .es registro anual', 'type' => 'service', 'price' => 9.90, 'vat' => 21, 'ref' => 'SERV-024'],
        ['name' => 'Email profesional mensual', 'type' => 'service', 'price' => 5.50, 'vat' => 21, 'ref' => 'SERV-025'],
        ['name' => 'Campaña Google Ads gestión', 'type' => 'service', 'price' => 250.00, 'vat' => 21, 'ref' => 'SERV-026'],
        ['name' => 'Diseño logotipo corporativo', 'type' => 'service', 'price' => 450.00, 'vat' => 21, 'ref' => 'SERV-027'],
        ['name' => 'Redacción contenidos blog (artículo)', 'type' => 'service', 'price' => 80.00, 'vat' => 21, 'ref' => 'SERV-028'],
        ['name' => 'Community Manager mensual', 'type' => 'service', 'price' => 500.00, 'vat' => 21, 'ref' => 'SERV-029'],
        ['name' => 'Vídeo corporativo producción', 'type' => 'service', 'price' => 1200.00, 'vat' => 21, 'ref' => 'SERV-030'],
    ];

    public function run(): void
    {
        $this->seedCompanyProfile();
        $this->seedPaymentTemplates();
        $bankAccount = $this->seedBankAccounts();
        $customers = $this->seedClients();
        $products = $this->seedProducts();
        $this->seedDocuments($customers, $products, $bankAccount);
        $this->seedExpenses($bankAccount);

        $this->command?->info('Demo data seeded successfully!');
    }

    private function seedCompanyProfile(): void
    {
        CompanyProfile::updateOrCreate(['id' => 1], [
            'legal_name' => 'Soluciones Digitales Factu01 SL',
            'trade_name' => 'Factu01',
            'nif' => 'B12345678',
            'address_street' => 'Calle de la Innovación, 42, 3ºA',
            'address_city' => 'Madrid',
            'address_postal_code' => '28001',
            'address_province' => 'Madrid',
            'address_country' => 'ES',
            'phone' => '+34 912 345 678',
            'email' => 'info@factu01.es',
            'website' => 'https://factu01.es',
            'tax_regime' => 'general',
            'irpf_rate' => 15.00,
            'catalog_enabled' => true,
        ]);

        $this->command?->info('Company profile created.');
    }

    private function seedPaymentTemplates(): void
    {
        // Skip if templates already exist (e.g. from TenantSeeder)
        if (PaymentTemplate::count() > 0) {
            $this->command?->info('Payment templates already exist, skipping.');
            return;
        }

        // 1. Contado (immediate, default)
        $contado = PaymentTemplate::create(['name' => 'Contado', 'is_default' => true]);
        PaymentTemplateLine::create([
            'payment_template_id' => $contado->id,
            'days_from_issue' => 0,
            'percentage' => 100.00,
            'sort_order' => 1,
        ]);

        // 2. 30 días
        $t30 = PaymentTemplate::create(['name' => '30 días', 'is_default' => false]);
        PaymentTemplateLine::create([
            'payment_template_id' => $t30->id,
            'days_from_issue' => 30,
            'percentage' => 100.00,
            'sort_order' => 1,
        ]);

        // 3. 30/60 días (50% each)
        $t3060 = PaymentTemplate::create(['name' => '30/60 días', 'is_default' => false]);
        PaymentTemplateLine::create([
            'payment_template_id' => $t3060->id,
            'days_from_issue' => 30,
            'percentage' => 50.00,
            'sort_order' => 1,
        ]);
        PaymentTemplateLine::create([
            'payment_template_id' => $t3060->id,
            'days_from_issue' => 60,
            'percentage' => 50.00,
            'sort_order' => 2,
        ]);

        // 4. 30/60/90 días (thirds)
        $t306090 = PaymentTemplate::create(['name' => '30/60/90 días', 'is_default' => false]);
        PaymentTemplateLine::create([
            'payment_template_id' => $t306090->id,
            'days_from_issue' => 30,
            'percentage' => 33.34,
            'sort_order' => 1,
        ]);
        PaymentTemplateLine::create([
            'payment_template_id' => $t306090->id,
            'days_from_issue' => 60,
            'percentage' => 33.33,
            'sort_order' => 2,
        ]);
        PaymentTemplateLine::create([
            'payment_template_id' => $t306090->id,
            'days_from_issue' => 90,
            'percentage' => 33.33,
            'sort_order' => 3,
        ]);

        $this->command?->info('4 payment templates created.');
    }

    private function seedBankAccounts(): ?BankAccount
    {
        $main = BankAccount::create([
            'name' => 'Cuenta corriente CaixaBank',
            'iban' => 'ES78 2100 5678 01 0123456789',
            'initial_balance' => 15000.00,
            'opening_date' => Carbon::now()->subMonths(14)->startOfMonth()->format('Y-m-d'),
            'is_default' => true,
            'is_active' => true,
        ]);

        BankAccount::create([
            'name' => 'Cuenta operativa BBVA',
            'iban' => 'ES91 0182 3456 71 0987654321',
            'initial_balance' => 5000.00,
            'opening_date' => Carbon::now()->subMonths(8)->startOfMonth()->format('Y-m-d'),
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->command?->info('2 bank accounts created.');

        return $main;
    }

    private function seedClients(): array
    {
        $clients = [];

        // 70 customers
        foreach (array_slice($this->customerNames, 0, 70) as $i => $name) {
            $clients[] = Client::create([
                'type' => 'customer',
                'legal_name' => $name,
                'trade_name' => $i % 3 === 0 ? explode(' ', $name)[0] . ' ' . explode(' ', $name)[1] : null,
                'nif' => $this->generateNif('B', $i + 1),
                'address_street' => $this->streets[array_rand($this->streets)] . ', ' . rand(1, 150),
                'address_city' => $this->provinces[array_rand($this->provinces)],
                'address_postal_code' => str_pad((string) rand(1000, 52999), 5, '0', STR_PAD_LEFT),
                'address_province' => $this->provinces[array_rand($this->provinces)],
                'address_country' => 'ES',
                'phone' => '+34 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'email' => 'contacto@' . $this->slugify($name) . '.es',
                'contact_person' => $this->randomPersonName(),
                'payment_terms_days' => [15, 30, 30, 30, 45, 60][array_rand([15, 30, 30, 30, 45, 60])],
                'payment_method' => ['transfer', 'transfer', 'transfer', 'card', 'cash'][array_rand(['transfer', 'transfer', 'transfer', 'card', 'cash'])],
                'iban' => 'ES' . rand(10, 99) . ' ' . rand(1000, 9999) . ' ' . rand(1000, 9999) . ' ' . rand(10, 99) . ' ' . rand(1000000000, 9999999999),
            ]);
        }

        // 30 suppliers
        foreach ($this->supplierNames as $i => $name) {
            $clients[] = Client::create([
                'type' => 'supplier',
                'legal_name' => $name,
                'trade_name' => $i % 2 === 0 ? explode(' ', $name)[0] : null,
                'nif' => $this->generateNif('A', $i + 100),
                'address_street' => $this->streets[array_rand($this->streets)] . ', ' . rand(1, 200),
                'address_city' => $this->provinces[array_rand($this->provinces)],
                'address_postal_code' => str_pad((string) rand(1000, 52999), 5, '0', STR_PAD_LEFT),
                'address_province' => $this->provinces[array_rand($this->provinces)],
                'address_country' => 'ES',
                'phone' => '+34 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'email' => 'info@' . $this->slugify($name) . '.com',
                'contact_person' => $this->randomPersonName(),
                'payment_terms_days' => [30, 30, 60][array_rand([30, 30, 60])],
                'payment_method' => 'transfer',
                'iban' => 'ES' . rand(10, 99) . ' ' . rand(1000, 9999) . ' ' . rand(1000, 9999) . ' ' . rand(10, 99) . ' ' . rand(1000000000, 9999999999),
            ]);
        }

        $this->command?->info(count($clients) . ' clients created (70 customers + 30 suppliers).');

        return $clients;
    }

    private function seedProducts(): array
    {
        $products = [];
        $placeholderColors = [
            '#6366F1', '#8B5CF6', '#EC4899', '#EF4444', '#F97316',
            '#EAB308', '#22C55E', '#14B8A6', '#06B6D4', '#3B82F6',
        ];

        foreach ($this->productNames as $i => $p) {
            $isPhysical = $p['type'] === 'product';
            $trackStock = $isPhysical && ($i % 5 !== 0); // ~80% of physical products track stock
            $stockQty = $trackStock ? rand(10, 200) : 0;
            $minStock = $trackStock ? rand(5, 20) : 0;

            $imagePath = null;
            if ($isPhysical) {
                $imagePath = $this->generatePlaceholderImage(
                    $p['ref'],
                    $placeholderColors[$i % count($placeholderColors)],
                );
            }

            $product = Product::create([
                'type' => $p['type'],
                'reference' => $p['ref'],
                'name' => $p['name'],
                'unit_price' => $p['price'],
                'vat_rate' => $p['vat'],
                'irpf_applicable' => $p['type'] === 'service' && rand(0, 3) === 0,
                'unit' => $p['unit'] ?? ($p['type'] === 'service' ? 'unit' : 'unit'),
                'track_stock' => $trackStock,
                'stock_quantity' => $stockQty,
                'minimum_stock' => $minStock,
                'image_path' => $imagePath,
            ]);

            if ($trackStock) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'initial',
                    'quantity' => $stockQty,
                    'stock_before' => 0,
                    'stock_after' => $stockQty,
                    'notes' => 'Stock inicial demo',
                ]);
            }

            $products[] = $product;
        }

        $trackCount = collect($products)->where('track_stock', true)->count();
        $imageCount = collect($products)->whereNotNull('image_path')->count();
        $this->command?->info(count($products) . " products created ({$trackCount} with stock, {$imageCount} with images).");

        return $products;
    }

    private function generatePlaceholderImage(string $ref, string $bgColor): string
    {
        $image = Image::create(400, 300)->fill($bgColor);

        $image->text($ref, 200, 150, function (FontFactory $font) {
            $font->size(36);
            $font->color('#FFFFFF');
            $font->align('center');
            $font->valign('middle');
        });

        $filename = 'demo_' . strtolower(str_replace('-', '_', $ref)) . '.jpg';
        $path = 'products/' . $filename;

        Storage::disk('local')->put($path, (string) $image->toJpeg(85));

        return $path;
    }

    private function seedDocuments(array $clients, array $products, ?BankAccount $bankAccount): void
    {
        $customers = array_filter($clients, fn ($c) => $c->type === 'customer');
        $suppliers = array_filter($clients, fn ($c) => $c->type === 'supplier');
        $customers = array_values($customers);
        $suppliers = array_values($suppliers);

        $series = DocumentSeries::all()->keyBy('document_type');

        // --- 100 Sales Invoices ---
        $invoiceCount = 0;
        for ($i = 0; $i < 100; $i++) {
            $issueDate = Carbon::now()->subDays(rand(1, 365));
            $customer = $customers[array_rand($customers)];
            $status = $this->weightedRandom(['finalized' => 15, 'sent' => 25, 'paid' => 40, 'partial' => 5, 'overdue' => 10, 'draft' => 5]);
            $dueDate = (clone $issueDate)->addDays($customer->payment_terms_days);

            $this->createDocumentWithLines(
                type: 'invoice',
                direction: 'issued',
                series: $series['invoice'] ?? null,
                client: $customer,
                products: $products,
                issueDate: $issueDate,
                dueDate: $dueDate,
                status: $status,
                invoiceType: 'F1',
                lineCount: rand(1, 6),
                number: $i + 1,
                bankAccount: $bankAccount,
            );
            $invoiceCount++;
        }
        $this->command?->info("{$invoiceCount} sales invoices created.");

        // --- 30 Quotes ---
        $quoteCount = 0;
        for ($i = 0; $i < 30; $i++) {
            $issueDate = Carbon::now()->subDays(rand(1, 180));
            $customer = $customers[array_rand($customers)];
            $status = $this->weightedRandom(['draft' => 30, 'finalized' => 40, 'sent' => 30]);

            $this->createDocumentWithLines(
                type: 'quote',
                direction: 'issued',
                series: $series['quote'] ?? null,
                client: $customer,
                products: $products,
                issueDate: $issueDate,
                dueDate: (clone $issueDate)->addDays(30),
                status: $status,
                lineCount: rand(2, 8),
                number: $i + 1,
            );
            $quoteCount++;
        }
        $this->command?->info("{$quoteCount} quotes created.");

        // --- 20 Delivery Notes ---
        $dnCount = 0;
        for ($i = 0; $i < 20; $i++) {
            $issueDate = Carbon::now()->subDays(rand(1, 120));
            $customer = $customers[array_rand($customers)];

            $this->createDocumentWithLines(
                type: 'delivery_note',
                direction: 'issued',
                series: $series['delivery_note'] ?? null,
                client: $customer,
                products: $products,
                issueDate: $issueDate,
                dueDate: null,
                status: $this->weightedRandom(['draft' => 20, 'finalized' => 80]),
                lineCount: rand(1, 5),
                number: $i + 1,
            );
            $dnCount++;
        }
        $this->command?->info("{$dnCount} delivery notes created.");

        // --- 50 Purchase Invoices ---
        $purchaseCount = 0;
        for ($i = 0; $i < 50; $i++) {
            $issueDate = Carbon::now()->subDays(rand(1, 365));
            $supplier = $suppliers[array_rand($suppliers)];
            $status = $this->weightedRandom(['draft' => 10, 'registered' => 30, 'paid' => 60]);

            $this->createDocumentWithLines(
                type: 'purchase_invoice',
                direction: 'received',
                series: $series['purchase_invoice'] ?? null,
                client: $supplier,
                products: $products,
                issueDate: $issueDate,
                dueDate: (clone $issueDate)->addDays(30),
                status: $status,
                lineCount: rand(1, 5),
                number: $i + 1,
                bankAccount: $bankAccount,
            );
            $purchaseCount++;
        }
        $this->command?->info("{$purchaseCount} purchase invoices created.");
    }

    private function createDocumentWithLines(
        string $type,
        string $direction,
        ?DocumentSeries $series,
        Client $client,
        array $products,
        Carbon $issueDate,
        ?Carbon $dueDate,
        string $status,
        int $lineCount,
        int $number,
        ?string $invoiceType = null,
        ?BankAccount $bankAccount = null,
    ): Document {
        $prefix = $series?->prefix ?? strtoupper(substr($type, 0, 4)) . '-2026-';
        $formattedNumber = $prefix . str_pad((string) $number, 5, '0', STR_PAD_LEFT);

        $doc = Document::create([
            'document_type' => $type,
            'invoice_type' => $invoiceType,
            'direction' => $direction,
            'series_id' => $series?->id,
            'number' => $status !== 'draft' ? $formattedNumber : null,
            'status' => $status,
            'client_id' => $client->id,
            'issue_date' => $issueDate->format('Y-m-d'),
            'due_date' => $dueDate?->format('Y-m-d'),
            'regime_key' => '01',
            'subtotal' => 0,
            'tax_base' => 0,
            'total_vat' => 0,
            'total_irpf' => 0,
            'total' => 0,
        ]);

        $subtotal = 0;
        $totalVat = 0;
        $totalIrpf = 0;

        for ($l = 0; $l < $lineCount; $l++) {
            $product = $products[array_rand($products)];
            $qty = $product['type'] === 'service'
                ? round(rand(1, 40) * 0.5, 1)
                : rand(1, 20);
            $unitPrice = (float) $product->unit_price;
            $vatRate = (float) $product->vat_rate;
            $irpfRate = $product->irpf_applicable ? 15.00 : 0.00;

            $lineSubtotal = round($qty * $unitPrice, 2);
            $discountPct = rand(0, 10) <= 2 ? rand(5, 15) : 0;
            $discountAmt = round($lineSubtotal * $discountPct / 100, 2);
            $lineTotal = round($lineSubtotal - $discountAmt, 2);
            $vatAmt = round($lineTotal * $vatRate / 100, 2);
            $irpfAmt = round($lineTotal * $irpfRate / 100, 2);

            DocumentLine::create([
                'document_id' => $doc->id,
                'product_id' => $product->id,
                'sort_order' => $l,
                'concept' => $product->name,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'unit' => $product->unit,
                'discount_percent' => $discountPct,
                'discount_amount' => $discountAmt,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmt,
                'irpf_rate' => $irpfRate,
                'irpf_amount' => $irpfAmt,
                'line_subtotal' => $lineSubtotal,
                'line_total' => $lineTotal,
            ]);

            $subtotal += $lineSubtotal;
            $totalVat += $vatAmt;
            $totalIrpf += $irpfAmt;
        }

        $taxBase = round(array_sum(
            DocumentLine::where('document_id', $doc->id)->pluck('line_total')->toArray()
        ), 2);
        $total = round($taxBase + $totalVat - $totalIrpf, 2);

        $doc->update([
            'subtotal' => round($subtotal, 2),
            'tax_base' => $taxBase,
            'total_vat' => round($totalVat, 2),
            'total_irpf' => round($totalIrpf, 2),
            'total' => $total,
        ]);

        // Update series next_number
        if ($series && $status !== 'draft') {
            $series->update(['next_number' => max($series->next_number, $number + 1)]);
        }

        // Create DocumentDueDates + TreasuryEntries for invoices (not quotes/delivery_notes)
        if ($status !== 'draft' && in_array($type, ['invoice', 'purchase_invoice'])) {
            $this->createDueDatesAndTreasury($doc, $dueDate ?? $issueDate->copy()->addDays(30), $total, $status, $direction, $formattedNumber, $bankAccount);
        }

        return $doc;
    }

    private function createDueDatesAndTreasury(
        Document $doc,
        Carbon $dueDate,
        float $total,
        string $status,
        string $direction,
        string $formattedNumber,
        ?BankAccount $bankAccount,
    ): void {
        $isPaid = $status === 'paid';
        $isPartial = $status === 'partial';
        $isOverdue = $status === 'overdue';

        // Most invoices get a single due date; ~20% get 2 due dates (split payment)
        $splitPayment = rand(1, 5) === 1 && abs($total) > 100;

        if ($splitPayment) {
            $amount1 = round($total / 2, 2);
            $amount2 = round($total - $amount1, 2);
            $dueDate2 = $dueDate->copy()->addDays(30);

            // First due date
            $dd1PaymentStatus = ($isPaid || $isPartial) ? 'paid' : 'pending';
            $dd1PaymentDate = $dd1PaymentStatus === 'paid'
                ? $dueDate->copy()->subDays(rand(0, 5))->format('Y-m-d')
                : null;

            $dd1 = DocumentDueDate::create([
                'document_id' => $doc->id,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $amount1,
                'percentage' => 50.00,
                'payment_status' => $dd1PaymentStatus,
                'payment_date' => $dd1PaymentDate,
                'sort_order' => 1,
            ]);

            // Second due date
            $dd2PaymentStatus = $isPaid ? 'paid' : 'pending';
            $dd2PaymentDate = $dd2PaymentStatus === 'paid'
                ? $dueDate2->copy()->subDays(rand(0, 5))->format('Y-m-d')
                : null;

            $dd2 = DocumentDueDate::create([
                'document_id' => $doc->id,
                'due_date' => $dueDate2->format('Y-m-d'),
                'amount' => $amount2,
                'percentage' => 50.00,
                'payment_status' => $dd2PaymentStatus,
                'payment_date' => $dd2PaymentDate,
                'sort_order' => 2,
            ]);

            // Treasury entries for paid due dates
            if ($bankAccount) {
                if ($dd1PaymentStatus === 'paid') {
                    $this->createTreasuryEntryForDueDate($dd1, $amount1, $direction, $formattedNumber, $bankAccount);
                }
                if ($dd2PaymentStatus === 'paid') {
                    $this->createTreasuryEntryForDueDate($dd2, $amount2, $direction, $formattedNumber, $bankAccount);
                }
            }
        } else {
            // Single due date
            $paymentStatus = $isPaid ? 'paid' : 'pending';
            $paymentDate = $isPaid
                ? $dueDate->copy()->subDays(rand(0, 10))->format('Y-m-d')
                : null;

            $dd = DocumentDueDate::create([
                'document_id' => $doc->id,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $total,
                'percentage' => 100.00,
                'payment_status' => $paymentStatus,
                'payment_date' => $paymentDate,
                'sort_order' => 1,
            ]);

            // Treasury entry for paid due date
            if ($bankAccount && $paymentStatus === 'paid') {
                $this->createTreasuryEntryForDueDate($dd, $total, $direction, $formattedNumber, $bankAccount);
            }
        }
    }

    private function createTreasuryEntryForDueDate(
        DocumentDueDate $dueDate,
        float $amount,
        string $direction,
        string $formattedNumber,
        BankAccount $bankAccount,
    ): void {
        $isCollection = $direction === 'issued';

        TreasuryEntry::create([
            'bank_account_id' => $bankAccount->id,
            'entry_date' => $dueDate->payment_date,
            'concept' => $isCollection
                ? "Cobro factura {$formattedNumber}"
                : "Pago factura compra {$formattedNumber}",
            'amount' => $isCollection ? abs($amount) : -abs($amount),
            'entry_type' => $isCollection ? TreasuryEntry::TYPE_COLLECTION : TreasuryEntry::TYPE_PAYMENT,
            'document_due_date_id' => $dueDate->id,
        ]);
    }

    private function seedExpenses(?BankAccount $bankAccount): void
    {
        // Ensure expense categories exist (tenant may have been created before the seeder added them)
        if (ExpenseCategory::count() === 0) {
            $defaultCategories = [
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
            foreach ($defaultCategories as $cat) {
                ExpenseCategory::create($cat);
            }
        }

        $categories = ExpenseCategory::all();
        $suppliers = Client::where('type', 'supplier')->get();

        $expenseConcepts = [
            'Alquiler oficina mes', 'Factura electricidad', 'Factura agua', 'Factura gas',
            'Factura teléfono e internet', 'Material de oficina', 'Suscripción software',
            'Combustible vehículo empresa', 'Seguro responsabilidad civil', 'Seguro local comercial',
            'Cuota autónomo RETA', 'Mantenimiento web y hosting', 'Publicidad Google Ads',
            'Publicidad redes sociales', 'Comida de trabajo', 'Viaje de negocios',
            'Taxi/VTC desplazamiento', 'Parking centro ciudad', 'Gestoría trimestral',
            'Asesoría fiscal', 'Nómina empleado', 'Formación profesional',
            'Licencia Microsoft 365', 'Licencia antivirus', 'Reparación impresora',
            'Limpieza oficinas', 'Cuota coworking', 'Envío paquetería urgente',
            'Compra cartuchos tinta', 'Reparación equipo informático',
            'Suscripción periódico digital', 'Cuota asociación empresarial',
            'Material promocional', 'Tarjetas de visita', 'Dominio web anual',
            'Certificado SSL', 'Servicio backup nube', 'Papelería y sobres',
            'Café y agua oficina', 'Flores decoración oficina',
        ];

        for ($i = 0; $i < 100; $i++) {
            $expenseDate = Carbon::now()->subDays(rand(1, 365));
            $category = $categories->random();
            $useSupplier = rand(0, 1) && $suppliers->isNotEmpty();
            $supplier = $useSupplier ? $suppliers->random() : null;
            $subtotal = round(rand(500, 250000) / 100, 2);
            $vatRate = [21.00, 21.00, 21.00, 10.00, 4.00, 0.00][array_rand([21.00, 21.00, 21.00, 10.00, 4.00, 0.00])];
            $vatAmount = round($subtotal * $vatRate / 100, 2);
            $irpfRate = rand(0, 4) === 0 ? 15.00 : 0.00;
            $irpfAmount = round($subtotal * $irpfRate / 100, 2);
            $total = round($subtotal + $vatAmount - $irpfAmount, 2);
            $isPaid = rand(0, 100) < 65;
            $paymentDate = $isPaid ? (clone $expenseDate)->addDays(rand(0, 30)) : null;

            $expense = Expense::create([
                'category_id' => $category->id,
                'supplier_client_id' => $supplier?->id,
                'supplier_name' => !$supplier ? 'Proveedor ' . rand(1, 50) : null,
                'invoice_number' => $useSupplier ? 'FV-' . rand(1000, 9999) . '-' . rand(1, 999) : null,
                'expense_date' => $expenseDate->format('Y-m-d'),
                'due_date' => (clone $expenseDate)->addDays(30)->format('Y-m-d'),
                'concept' => $expenseConcepts[array_rand($expenseConcepts)],
                'subtotal' => $subtotal,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'irpf_rate' => $irpfRate,
                'irpf_amount' => $irpfAmount,
                'total' => $total,
                'payment_status' => $isPaid ? 'paid' : 'pending',
                'payment_date' => $paymentDate?->format('Y-m-d'),
                'payment_method' => $isPaid ? ['transfer', 'card', 'cash'][array_rand(['transfer', 'card', 'cash'])] : null,
            ]);

            // Treasury entry for paid expenses
            if ($isPaid && $bankAccount) {
                TreasuryEntry::create([
                    'bank_account_id' => $bankAccount->id,
                    'entry_date' => $paymentDate->format('Y-m-d'),
                    'concept' => "Pago gasto - {$expense->concept}",
                    'amount' => -abs($total),
                    'entry_type' => TreasuryEntry::TYPE_PAYMENT,
                    'expense_id' => $expense->id,
                ]);
            }
        }

        $this->command?->info('100 expenses created.');
    }

    private function generateNif(string $prefix, int $seed): string
    {
        return $prefix . str_pad((string) (10000000 + $seed * 7919 % 90000000), 8, '0', STR_PAD_LEFT);
    }

    private function randomPersonName(): string
    {
        $firstNames = ['María', 'José', 'Antonio', 'Ana', 'Manuel', 'Laura', 'Francisco', 'Carmen', 'David', 'Marta', 'Carlos', 'Lucía', 'Pedro', 'Elena', 'Javier', 'Isabel', 'Miguel', 'Rosa', 'Pablo', 'Pilar'];
        $lastNames = ['García', 'Martínez', 'López', 'Sánchez', 'González', 'Rodríguez', 'Fernández', 'Pérez', 'Gómez', 'Ruiz', 'Díaz', 'Hernández', 'Moreno', 'Muñoz', 'Álvarez', 'Romero', 'Jiménez', 'Torres', 'Navarro', 'Domínguez'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[áàä]/', 'a', $text);
        $text = preg_replace('/[éèë]/', 'e', $text);
        $text = preg_replace('/[íìï]/', 'i', $text);
        $text = preg_replace('/[óòö]/', 'o', $text);
        $text = preg_replace('/[úùü]/', 'u', $text);
        $text = preg_replace('/ñ/', 'n', $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $current = 0;
        foreach ($weights as $key => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $key;
            }
        }
        return array_key_first($weights);
    }
}
