export interface VatRate {
    id: number;
    name: string;
    rate: number;
    surcharge_rate: number;
    is_default: boolean;
    is_exempt: boolean;
    sort_order: number;
}
