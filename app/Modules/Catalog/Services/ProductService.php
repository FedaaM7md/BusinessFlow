<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Catalog\Services;

use App\Models\User;
use App\Modules\Catalog\Models\Product;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Core\Services\Money;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data, User $actor): Product
    {
        return DB::transaction(function () use ($data, $actor) {
            $data = $this->normalize($data);
            $data['created_by'] = $actor->id;
            $product = Product::create($data);

            $this->activity->log('catalog', 'created', 'Product '.$product->name.' created', $product, [
                'name' => ['from' => null, 'to' => $product->name],
                'price_minor' => ['from' => null, 'to' => $product->price_minor],
            ]);

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->fill($this->normalize($data))->save();
            $changes = $this->activity->changedFields($product);
            if ($changes !== []) {
                $this->activity->log('catalog', 'updated', 'Product '.$product->name.' updated', $product, $changes);
            }

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->delete();
            $this->activity->log('catalog', 'deleted', 'Product '.$product->name.' deleted', $product);
        });
    }

    private function normalize(array $data): array
    {
        $data['price_minor'] = Money::toMinor($data['price']);
        $data['cost_minor'] = Money::toMinor($data['cost'] ?? '0');
        $data['tax_rate'] = Money::percentToBasisPoints($data['tax_rate']);
        $data['low_stock_threshold'] = (float) ($data['low_stock_threshold'] ?? 0);
        $data['is_tracked'] = $data['type'] === 'service' ? false : (bool) $data['is_tracked'];
        unset($data['price'], $data['cost']);

        return $data;
    }
}
