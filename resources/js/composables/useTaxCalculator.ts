/**
 * Client-side tax calculator mirroring TaxCalculatorService.php
 */

export interface LineInput {
    product_id?: number | null;
    concept: string;
    description?: string;
    quantity: number;
    unit_price: number;
    unit?: string;
    discount_percent: number;
    vat_rate: number;
    exemption_code?: string;
    irpf_rate: number;
    surcharge_rate: number;
}

export interface CalculatedLine extends LineInput {
    line_subtotal: number;
    discount_amount: number;
    line_total: number;
    vat_amount: number;
    irpf_amount: number;
    surcharge_amount: number;
}

export interface VatBreakdownEntry {
    rate: number;
    base: number;
    vat: number;
    surcharge_rate: number;
    surcharge: number;
}

export interface DocumentTotals {
    subtotal: number;
    total_discount: number;
    global_discount_amount: number;
    tax_base: number;
    total_vat: number;
    total_irpf: number;
    total_surcharge: number;
    total: number;
    vat_breakdown: VatBreakdownEntry[];
}

const FALLBACK_SURCHARGE_RATES: Record<string, number> = {
    '21.00': 5.2,
    '10.00': 1.4,
    '4.00': 0.5,
    '0.00': 0,
};

let surchargeRatesMap: Record<string, number> = { ...FALLBACK_SURCHARGE_RATES };

export function initSurchargeMap(vatRates: { rate: number; surcharge_rate: number }[]): void {
    if (vatRates && vatRates.length > 0) {
        surchargeRatesMap = {};
        for (const vr of vatRates) {
            const key = Number(vr.rate).toFixed(2);
            surchargeRatesMap[key] = Number(vr.surcharge_rate);
        }
    }
}

function round2(value: number): number {
    return Math.round((value + Number.EPSILON) * 100) / 100;
}

export function getSurchargeRate(vatRate: number): number {
    const key = vatRate.toFixed(2);
    return surchargeRatesMap[key] ?? 0;
}

export function calculateLine(line: LineInput): CalculatedLine {
    const quantity = line.quantity || 0;
    const unitPrice = line.unit_price || 0;
    const discountPercent = line.discount_percent || 0;
    const vatRate = line.vat_rate ?? 21;
    const irpfRate = line.irpf_rate || 0;
    const surchargeRate = line.surcharge_rate || 0;

    const lineSubtotal = round2(quantity * unitPrice);
    const discountAmount = round2(lineSubtotal * discountPercent / 100);
    const lineTotal = round2(lineSubtotal - discountAmount);
    const vatAmount = round2(lineTotal * vatRate / 100);
    const irpfAmount = round2(lineTotal * irpfRate / 100);
    const surchargeAmount = round2(lineTotal * surchargeRate / 100);

    return {
        ...line,
        line_subtotal: lineSubtotal,
        discount_amount: discountAmount,
        line_total: lineTotal,
        vat_amount: vatAmount,
        irpf_amount: irpfAmount,
        surcharge_amount: surchargeAmount,
    };
}

export function calculateDocument(lines: LineInput[], globalDiscountPercent: number = 0): DocumentTotals {
    let subtotal = 0;
    let totalLineDiscount = 0;
    const calculatedLines: CalculatedLine[] = [];

    for (const line of lines) {
        const calc = calculateLine(line);
        calculatedLines.push(calc);
        subtotal = round2(subtotal + calc.line_subtotal);
        totalLineDiscount = round2(totalLineDiscount + calc.discount_amount);
    }

    const taxBaseBeforeGlobal = round2(subtotal - totalLineDiscount);
    const globalDiscountAmount = round2(taxBaseBeforeGlobal * globalDiscountPercent / 100);
    const taxBase = round2(taxBaseBeforeGlobal - globalDiscountAmount);

    const globalFactor = taxBaseBeforeGlobal > 0 ? taxBase / taxBaseBeforeGlobal : 0;

    const vatMap: Record<string, VatBreakdownEntry> = {};
    let totalVat = 0;
    let totalIrpf = 0;
    let totalSurcharge = 0;

    for (const line of calculatedLines) {
        const adjustedBase = round2(line.line_total * globalFactor);
        const vatRate = (line.vat_rate ?? 21).toFixed(2);
        const irpfRate = line.irpf_rate || 0;
        const surchargeRate = line.surcharge_rate || 0;

        const lineVat = round2(adjustedBase * parseFloat(vatRate) / 100);
        const lineIrpf = round2(adjustedBase * irpfRate / 100);
        const lineSurcharge = round2(adjustedBase * surchargeRate / 100);

        if (!vatMap[vatRate]) {
            vatMap[vatRate] = {
                rate: parseFloat(vatRate),
                base: 0,
                vat: 0,
                surcharge_rate: surchargeRate,
                surcharge: 0,
            };
        }

        vatMap[vatRate].base = round2(vatMap[vatRate].base + adjustedBase);
        vatMap[vatRate].vat = round2(vatMap[vatRate].vat + lineVat);
        vatMap[vatRate].surcharge = round2(vatMap[vatRate].surcharge + lineSurcharge);

        totalVat = round2(totalVat + lineVat);
        totalIrpf = round2(totalIrpf + lineIrpf);
        totalSurcharge = round2(totalSurcharge + lineSurcharge);
    }

    const total = round2(taxBase + totalVat - totalIrpf + totalSurcharge);
    const totalDiscount = round2(totalLineDiscount + globalDiscountAmount);

    return {
        subtotal,
        total_discount: totalDiscount,
        global_discount_amount: globalDiscountAmount,
        tax_base: taxBase,
        total_vat: totalVat,
        total_irpf: totalIrpf,
        total_surcharge: totalSurcharge,
        total,
        vat_breakdown: Object.values(vatMap),
    };
}
