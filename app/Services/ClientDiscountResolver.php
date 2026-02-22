<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientDiscount;
use App\Models\Product;

class ClientDiscountResolver
{
    /**
     * Resolve the best applicable discount for a client+product combination.
     * Priority: family > type > agreement > general (most specific wins).
     */
    public function resolve(Client $client, Product $product, float $lineAmount = 0): ?float
    {
        $discounts = $client->discounts()
            ->where(function ($q) use ($product) {
                $q->where('discount_type', 'general')
                  ->orWhere(function ($q2) {
                      $q2->where('discount_type', 'agreement');
                  })
                  ->orWhere(function ($q2) use ($product) {
                      $q2->where('discount_type', 'type')
                         ->where('product_type', $product->type);
                  })
                  ->orWhere(function ($q2) use ($product) {
                      $q2->where('discount_type', 'family')
                         ->where('product_family_id', $product->product_family_id);
                  });
            })
            ->get();

        // Filter valid discounts
        $validDiscounts = $discounts->filter(function (ClientDiscount $d) use ($lineAmount) {
            if (! $d->isValidNow()) {
                return false;
            }

            // Agreement discounts check min_amount
            if ($d->discount_type === 'agreement' && $d->min_amount && $lineAmount < (float) $d->min_amount) {
                return false;
            }

            // Family discounts require product to have that family
            if ($d->discount_type === 'family' && ! $d->product_family_id) {
                return false;
            }

            return true;
        });

        if ($validDiscounts->isEmpty()) {
            return null;
        }

        // Priority order: family > type > agreement > general
        $priority = ['family' => 4, 'type' => 3, 'agreement' => 2, 'general' => 1];

        $best = $validDiscounts->sortByDesc(function (ClientDiscount $d) use ($priority) {
            return $priority[$d->discount_type] ?? 0;
        })->first();

        return $best ? (float) $best->discount_percent : null;
    }
}
