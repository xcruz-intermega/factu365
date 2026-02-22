export interface ClientDiscountData {
    id: number;
    discount_type: 'general' | 'agreement' | 'type' | 'family';
    discount_percent: number;
    product_type: string | null;
    product_family_id: number | null;
    min_amount: number | null;
    valid_from: string | null;
    valid_to: string | null;
    notes: string | null;
}

interface ProductForDiscount {
    id: number;
    type: string;
    product_family_id?: number | null;
}

const PRIORITY: Record<string, number> = {
    family: 4,
    type: 3,
    agreement: 2,
    general: 1,
};

function isValidNow(discount: ClientDiscountData): boolean {
    const today = new Date().toISOString().split('T')[0];

    if (discount.valid_from && today < discount.valid_from) {
        return false;
    }

    if (discount.valid_to && today > discount.valid_to) {
        return false;
    }

    return true;
}

/**
 * Resolve the best applicable discount for a client+product combination.
 * Priority: family > type > agreement > general (most specific wins).
 */
export function resolveClientDiscount(
    discounts: ClientDiscountData[],
    product: ProductForDiscount,
    lineAmount: number = 0,
): number | null {
    const validDiscounts = discounts.filter(d => {
        if (!isValidNow(d)) return false;

        switch (d.discount_type) {
            case 'general':
                return true;
            case 'agreement':
                if (d.min_amount && lineAmount < d.min_amount) return false;
                return true;
            case 'type':
                return d.product_type === product.type;
            case 'family':
                if (!product.product_family_id || !d.product_family_id) return false;
                return d.product_family_id === product.product_family_id;
            default:
                return false;
        }
    });

    if (validDiscounts.length === 0) return null;

    // Sort by priority descending, pick the highest
    validDiscounts.sort((a, b) => (PRIORITY[b.discount_type] ?? 0) - (PRIORITY[a.discount_type] ?? 0));

    return Number(validDiscounts[0].discount_percent);
}
