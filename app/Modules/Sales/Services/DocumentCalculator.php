<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Services;

class DocumentCalculator
{
    /**
     * Calculate line and document totals in integer piasters.
     *
     * @param  array<int, array{product_id?: int|null, description: string, quantity: string|int|float, unit_price_minor: int, tax_rate: int}>  $lines
     * @return array{lines: array<int, array<string, mixed>>, subtotal_minor: int, discount_minor: int, tax_minor: int, total_minor: int}
     */
    public function calculate(array $lines, int $discountMinor = 0): array
    {
        $calculated = [];
        $subtotal = 0;

        foreach (array_values($lines) as $index => $line) {
            $quantity = $this->normalizeQuantity($line['quantity'] ?? 0);
            $price = max(0, (int) ($line['unit_price_minor'] ?? 0));
            $lineSubtotal = $this->roundMinor(bcmul($quantity, (string) $price, 4));
            $subtotal += $lineSubtotal;

            $calculated[] = [
                'product_id' => $line['product_id'] ?? null,
                'description' => trim((string) $line['description']),
                'quantity' => $quantity,
                'unit_price_minor' => $price,
                'tax_rate' => max(0, min(10000, (int) ($line['tax_rate'] ?? 0))),
                'subtotal_minor' => $lineSubtotal,
                'sort_order' => $index,
            ];
        }

        $discount = max(0, min($discountMinor, $subtotal));
        $remainingDiscount = $discount;
        $lineCount = count($calculated);

        foreach ($calculated as $index => &$line) {
            if ($lineCount === 1) {
                $allocatedDiscount = $remainingDiscount;
            } elseif ($index === $lineCount - 1) {
                $allocatedDiscount = min($remainingDiscount, $line['subtotal_minor']);
            } else {
                $allocatedDiscount = $subtotal > 0
                    ? $this->roundMinor(bcdiv(bcmul((string) $discount, (string) $line['subtotal_minor'], 6), (string) $subtotal, 6))
                    : 0;
                $allocatedDiscount = min($allocatedDiscount, $line['subtotal_minor'], $remainingDiscount);
            }

            $line['discount_minor'] = $allocatedDiscount;
            $remainingDiscount -= $allocatedDiscount;
        }
        unset($line);

        // Absorb tiny rounding remainders into lines that still have discount capacity.
        foreach ($calculated as &$line) {
            if ($remainingDiscount <= 0) {
                break;
            }
            $capacity = $line['subtotal_minor'] - $line['discount_minor'];
            $extra = min($capacity, $remainingDiscount);
            $line['discount_minor'] += $extra;
            $remainingDiscount -= $extra;
        }
        unset($line);

        $tax = 0;
        foreach ($calculated as &$line) {
            $taxable = $line['subtotal_minor'] - $line['discount_minor'];
            $lineTax = $this->roundMinor(bcdiv(bcmul((string) $taxable, (string) $line['tax_rate'], 6), '10000', 6));

            $line['tax_minor'] = $lineTax;
            $line['total_minor'] = $taxable + $lineTax;
            $tax += $lineTax;
        }
        unset($line);

        return [
            'lines' => $calculated,
            'subtotal_minor' => $subtotal,
            'discount_minor' => $discount,
            'tax_minor' => $tax,
            'total_minor' => $subtotal - $discount + $tax,
        ];
    }

    public function normalizeQuantity(string|int|float|null $quantity): string
    {
        $value = trim((string) ($quantity ?? '0'));
        $value = str_replace(',', '.', $value);
        if (! preg_match('/^-?\d{1,9}(?:\.\d{1,2})?$/', $value)) {
            return '0';
        }

        $negative = str_starts_with($value, '-');
        $absolute = ltrim($value, '-');

        if (! str_contains($absolute, '.')) {
            $normalized = ltrim($absolute, '0') ?: '0';
        } else {
            $normalized = rtrim(rtrim($absolute, '0'), '.') ?: '0';
        }

        return ($negative && $normalized !== '0' ? '-' : '').$normalized;
    }

    private function roundMinor(string $value): int
    {
        $negative = str_starts_with($value, '-');
        $abs = ltrim($value, '+-');
        $rounded = (int) bcadd($abs, '0.5', 0);

        return $negative ? -$rounded : $rounded;
    }
}
