export interface SampleLine {
    concept: string;
    description: string | null;
    quantity: number;
    unit_price: number;
    unit: string;
    discount_percent: number;
    vat_rate: number;
    line_subtotal: number;
    line_total: number;
    vat_amount: number;
    surcharge_amount: number;
}

export interface SampleClient {
    legal_name: string;
    trade_name: string;
    nif: string;
    address_street: string;
    address_postal_code: string;
    address_city: string;
    address_province: string;
}

export interface SampleCompany {
    legal_name: string;
    trade_name: string;
    nif: string;
    address_street: string;
    address_postal_code: string;
    address_city: string;
    address_province: string;
    phone: string;
    email: string;
    website: string;
    software_name: string;
    software_version: string;
    logo_path: string | null;
}

export interface SampleDocument {
    number: string;
    document_type: string;
    issue_date: string;
    due_date: string;
    operation_date: string;
    notes: string;
    footer_text: string;
    subtotal: number;
    total_discount: number;
    tax_base: number;
    total_vat: number;
    total_surcharge: number;
    total_irpf: number;
    total: number;
    client: SampleClient;
    correctedDocument: null;
    invoice_type: null;
}

export interface VatBreakdownEntry {
    vat_rate: number;
    base: number;
    vat: number;
    surcharge: number;
}

export interface SampleData {
    document: SampleDocument;
    company: SampleCompany;
    lines: SampleLine[];
    vatBreakdown: VatBreakdownEntry[];
    typeLabel: string;
}

function makeLine(
    concept: string,
    description: string | null,
    qty: number,
    price: number,
    discount: number,
    vat: number,
): SampleLine {
    const subtotal = qty * price;
    const discountAmount = subtotal * (discount / 100);
    const base = subtotal - discountAmount;
    const vatAmount = base * (vat / 100);
    return {
        concept,
        description,
        quantity: qty,
        unit_price: price,
        unit: 'unidad',
        discount_percent: discount,
        vat_rate: vat,
        line_subtotal: base,
        line_total: base + vatAmount,
        vat_amount: vatAmount,
        surcharge_amount: 0,
    };
}

export const sampleInvoice: SampleData = {
    document: {
        number: 'FACT-2026-00001',
        document_type: 'invoice',
        issue_date: '2026-02-26',
        due_date: '2026-03-28',
        operation_date: '2026-02-26',
        notes: 'Ejemplo de observaciones del documento.',
        footer_text: 'Forma de pago: Transferencia bancaria 30 días.',
        subtotal: 1500.00,
        total_discount: 50.00,
        tax_base: 1450.00,
        total_vat: 304.50,
        total_surcharge: 0,
        total_irpf: 0,
        total: 1754.50,
        client: {
            legal_name: 'Empresa Cliente S.L.',
            trade_name: '',
            nif: 'B12345678',
            address_street: 'Calle Ejemplo, 42',
            address_postal_code: '28001',
            address_city: 'Madrid',
            address_province: 'Madrid',
        },
        correctedDocument: null,
        invoice_type: null,
    },
    company: {
        legal_name: 'Mi Empresa S.L.',
        trade_name: 'Mi Empresa',
        nif: 'B87654321',
        address_street: 'Av. de la Constitución, 10',
        address_postal_code: '08001',
        address_city: 'Barcelona',
        address_province: 'Barcelona',
        phone: '934 567 890',
        email: 'info@miempresa.es',
        website: 'www.miempresa.es',
        software_name: 'Factu365',
        software_version: '1.0',
        logo_path: null,
    },
    lines: [
        makeLine('Servicio de diseño web', 'Diseño responsive completo', 1, 600.00, 0, 21),
        makeLine('Hosting anual', 'Plan profesional 12 meses', 1, 400.00, 0, 21),
        makeLine('Mantenimiento mensual', null, 5, 100.00, 10, 21),
    ],
    vatBreakdown: [
        { vat_rate: 21, base: 1450.00, vat: 304.50, surcharge: 0 },
    ],
    typeLabel: 'Factura',
};
