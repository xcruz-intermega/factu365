<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class StockService
{
    /**
     * Create a stock movement and update the product's stock quantity.
     */
    public function moveStock(
        Product $product,
        float $quantity,
        string $type,
        ?Document $document = null,
        ?DocumentLine $line = null,
        ?string $notes = null,
    ): StockMovement {
        $stockBefore = (float) $product->stock_quantity;
        $stockAfter = $stockBefore + $quantity;

        if ($quantity < 0 && ! $product->allow_negative_stock && $stockAfter < 0) {
            throw new InsufficientStockException(
                productId: $product->id,
                productName: $product->name,
                requiredQuantity: abs($quantity),
                availableQuantity: $stockBefore,
            );
        }

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'document_id' => $document?->id,
            'document_line_id' => $line?->id,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);

        $product->update(['stock_quantity' => $stockAfter]);

        return $movement;
    }

    /**
     * Process all lines of a document, adjusting stock accordingly.
     *
     * @param  string  $direction  'out' for sales/delivery notes, 'in' for purchases
     */
    public function processDocumentLines(Document $document, string $direction): void
    {
        $document->loadMissing(['lines.product.components.component']);

        $type = $direction === 'out' ? 'sale' : 'purchase';

        foreach ($document->lines as $line) {
            if (! $line->product_id) {
                continue;
            }

            $product = $line->product;

            if (! $product || ! $product->tracksStock()) {
                continue;
            }

            if ($product->usesComponentStock()) {
                // Deduct from each component
                foreach ($product->components as $comp) {
                    $component = $comp->component;
                    if ($component && $component->tracksStock()) {
                        $qty = $direction === 'out'
                            ? -(float) $line->quantity * (float) $comp->quantity
                            : (float) $line->quantity * (float) $comp->quantity;

                        $this->moveStock($component, $qty, $type, $document, $line);
                    }
                }
            } else {
                $qty = $direction === 'out'
                    ? -(float) $line->quantity
                    : (float) $line->quantity;

                $this->moveStock($product, $qty, $type, $document, $line);
            }
        }
    }

    /**
     * Reverse all stock movements for a document (used for rectificatives).
     */
    public function reverseDocumentMovements(Document $document): void
    {
        $movements = StockMovement::where('document_id', $document->id)->get();

        foreach ($movements as $movement) {
            $product = $movement->product;
            if (! $product) {
                continue;
            }

            $this->moveStock(
                product: $product,
                quantity: -(float) $movement->quantity,
                type: 'return',
                document: $document,
                notes: "Reversal of movement #{$movement->id}",
            );
        }
    }

    /**
     * Check if all lines in a document can be fulfilled with current stock.
     *
     * @return array Array of products with insufficient stock [{product_id, name, required, available}]
     */
    public function canFulfill(Document $document): array
    {
        $document->loadMissing(['lines.product.components.component']);
        $insufficient = [];

        // Aggregate required quantities per product
        $required = [];

        foreach ($document->lines as $line) {
            if (! $line->product_id) {
                continue;
            }

            $product = $line->product;
            if (! $product || ! $product->tracksStock()) {
                continue;
            }

            if ($product->usesComponentStock()) {
                foreach ($product->components as $comp) {
                    $component = $comp->component;
                    if ($component && $component->tracksStock() && ! $component->allow_negative_stock) {
                        $qty = (float) $line->quantity * (float) $comp->quantity;
                        $required[$component->id] = ($required[$component->id] ?? 0) + $qty;
                    }
                }
            } else {
                if (! $product->allow_negative_stock) {
                    $required[$product->id] = ($required[$product->id] ?? 0) + (float) $line->quantity;
                }
            }
        }

        foreach ($required as $productId => $qty) {
            $product = Product::find($productId);
            if ($product && (float) $product->stock_quantity < $qty) {
                $insufficient[] = [
                    'product_id' => $productId,
                    'name' => $product->name,
                    'required' => $qty,
                    'available' => (float) $product->stock_quantity,
                ];
            }
        }

        return $insufficient;
    }
}
