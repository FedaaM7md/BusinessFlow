<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Inventory\Services;

use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['is_default'])) {
                Warehouse::query()->update(['is_default' => false]);
            }

            $warehouse = Warehouse::create($data);
            $this->activity->log('inventory', 'created', 'Warehouse '.$warehouse->name.' created', $warehouse);

            return $warehouse;
        });
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            if (! empty($data['is_default'])) {
                Warehouse::query()->whereKeyNot($warehouse->id)->update(['is_default' => false]);
            }

            $warehouse->fill($data)->save();
            $this->activity->log('inventory', 'updated', 'Warehouse '.$warehouse->name.' updated', $warehouse, $this->activity->changedFields($warehouse));

            return $warehouse->refresh();
        });
    }

    public function delete(Warehouse $warehouse): void
    {
        DB::transaction(function () use ($warehouse) {
            if ($warehouse->movements()->exists()) {
                throw ValidationException::withMessages(['delete' => __('messages.warehouse_has_stock')]);
            }

            if ($warehouse->is_default) {
                throw ValidationException::withMessages(['delete' => __('messages.default_warehouse')]);
            }

            $warehouse->delete();
            $this->activity->log('inventory', 'deleted', 'Warehouse '.$warehouse->name.' deleted', $warehouse);
        });
    }
}
