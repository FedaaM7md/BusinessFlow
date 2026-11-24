<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Inventory\Services;

use App\Models\User;
use App\Modules\Catalog\Models\Product;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Services\DocumentCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly DocumentCalculator $calculator,
    ) {}

    public function stockIn(
        Product $product,
        Warehouse $warehouse,
        string|int|float $quantity,
        User $actor,
        ?int $unitCostMinor = null,
        ?string $date = null,
        ?string $note = null,
        ?Model $reference = null,
    ): StockMovement {
        $quantity = $this->positiveQuantity($quantity);

        return $this->record($product, $warehouse, 'in', $quantity, $actor, $unitCostMinor, $date, $note, $reference);
    }

    public function stockOut(
        Product $product,
        Warehouse $warehouse,
        string|int|float $quantity,
        User $actor,
        ?string $date = null,
        ?string $note = null,
        ?Model $reference = null,
    ): StockMovement {
        $quantity = $this->positiveQuantity($quantity);
        $this->ensureAvailable($product, $warehouse, $quantity, 'stock');

        return $this->record($product, $warehouse, 'out', -$quantity, $actor, null, $date, $note, $reference);
    }

    public function adjust(
        Product $product,
        Warehouse $warehouse,
        string|int|float $quantity,
        User $actor,
        ?string $note = null,
        ?string $date = null,
    ): StockMovement {
        $quantity = (float) $this->normalizeQuantity($quantity);
        if ($quantity == 0.0) {
            throw ValidationException::withMessages(['quantity' => __('messages.quantity_nonzero')]);
        }

        if ($quantity < 0) {
            $this->ensureAvailable($product, $warehouse, abs($quantity), 'quantity');
        }

        return $this->record($product, $warehouse, 'adjustment', $quantity, $actor, null, $date, $note);
    }

    public function transfer(
        Product $product,
        Warehouse $from,
        Warehouse $to,
        string|int|float $quantity,
        User $actor,
        ?string $note = null,
        ?string $date = null,
    ): void {
        if ($from->id === $to->id) {
            throw ValidationException::withMessages(['target_warehouse_id' => __('messages.transfer_same_warehouse')]);
        }

        $quantity = $this->positiveQuantity($quantity);
        $this->ensureAvailable($product, $from, $quantity, 'quantity');

        DB::transaction(function () use ($product, $from, $to, $quantity, $actor, $note, $date) {
            $group = (string) Str::uuid();
            $this->record($product, $from, 'transfer', -$quantity, $actor, null, $date, $note, null, $group);
            $this->record($product, $to, 'transfer', $quantity, $actor, null, $date, $note, null, $group);
        });
    }

    public function issueInvoice(Invoice $invoice, User $actor): void
    {
        DB::transaction(function () use ($invoice, $actor) {
            if (! $invoice->warehouse_id) {
                $invoice->update(['warehouse_id' => Warehouse::default()->id]);
                $invoice->refresh();
            }

            $warehouse = Warehouse::findOrFail($invoice->warehouse_id);
            $tracked = [];

            foreach ($invoice->lines as $line) {
                $product = $line->product;
                if (! $product || ! $product->is_tracked) {
                    continue;
                }

                $tracked[$product->id]['product'] = $product;
                $tracked[$product->id]['quantity'] = ($tracked[$product->id]['quantity'] ?? 0) + (float) $line->quantity;
            }

            foreach ($tracked as $item) {
                $this->ensureAvailable($item['product'], $warehouse, $item['quantity'], 'stock');
            }

            foreach ($invoice->lines as $line) {
                $product = $line->product;
                if (! $product || ! $product->is_tracked) {
                    continue;
                }

                $this->record(
                    $product,
                    $warehouse,
                    'out',
                    -((float) $line->quantity),
                    $actor,
                    null,
                    $invoice->issue_date->format('Y-m-d'),
                    __('messages.invoice_stock_note', ['number' => $invoice->number]),
                    $invoice,
                );
            }
        });
    }

    public function reverseInvoice(Invoice $invoice, User $actor): void
    {
        DB::transaction(function () use ($invoice, $actor) {
            $movements = StockMovement::query()
                ->where('reference_type', $invoice->getMorphClass())
                ->where('reference_id', $invoice->getKey())
                ->where('type', 'out')
                ->where('quantity', '<', 0)
                ->get();

            foreach ($movements as $movement) {
                if (StockMovement::query()
                    ->where('reference_type', $invoice->getMorphClass())
                    ->where('reference_id', $invoice->getKey())
                    ->where('type', 'in')
                    ->where('note', 'like', 'Reversal%'.$invoice->number.'%')
                    ->where('product_id', $movement->product_id)
                    ->exists()) {
                    continue;
                }

                $warehouse = Warehouse::findOrFail($movement->warehouse_id);
                $this->record(
                    $movement->product,
                    $warehouse,
                    'in',
                    abs((float) $movement->quantity),
                    $actor,
                    $movement->unit_cost_minor,
                    now()->format('Y-m-d'),
                    'Reversal '.$invoice->number,
                    $invoice,
                );
            }
        });
    }

    public function available(Product $product, Warehouse $warehouse): float
    {
        return (float) StockMovement::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->sum('quantity');
    }

    private function record(
        Product $product,
        Warehouse $warehouse,
        string $type,
        float $quantity,
        User $actor,
        ?int $unitCostMinor = null,
        ?string $date = null,
        ?string $note = null,
        ?Model $reference = null,
        ?string $transferGroup = null,
    ): StockMovement {
        $movement = StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => $type,
            'quantity' => $quantity,
            'unit_cost_minor' => $unitCostMinor,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'transfer_group' => $transferGroup,
            'note' => $note,
            'created_by' => $actor->id,
            'moved_at' => $date ?? now()->format('Y-m-d'),
        ]);

        $this->activity->log(
            'inventory',
            'stock_'.$type,
            ucfirst($type).' stock for '.$product->name.' ('.$this->calculator->normalizeQuantity($quantity).')',
            $movement,
            ['quantity' => ['from' => null, 'to' => $quantity]],
        );

        return $movement;
    }

    private function ensureAvailable(Product $product, Warehouse $warehouse, float $quantity, string $errorKey): void
    {
        if ($this->available($product, $warehouse) + 0.0001 < $quantity) {
            throw ValidationException::withMessages([
                $errorKey => __('messages.insufficient_stock', ['product' => $product->name]),
            ]);
        }
    }

    private function positiveQuantity(string|int|float $quantity): float
    {
        $quantity = (float) $this->normalizeQuantity($quantity);
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => __('messages.quantity_positive')]);
        }

        return $quantity;
    }

    private function normalizeQuantity(string|int|float $quantity): string
    {
        $normalized = $this->calculator->normalizeQuantity($quantity);
        if ($normalized === '0' && (float) $quantity !== 0.0) {
            throw ValidationException::withMessages(['quantity' => __('validation.numeric', ['attribute' => __('fields.quantity')])]);
        }

        return $normalized;
    }
}
