<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Suppliers\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Suppliers\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data, User $actor): Supplier
    {
        return DB::transaction(function () use ($data, $actor) {
            $next = ((int) Supplier::withTrashed()->max('id')) + 1;
            $data['created_by'] = $actor->id;
            $data['code'] = 'SUP-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);

            $supplier = Supplier::create($data);
            $this->activity->log('suppliers', 'created', 'Supplier '.$supplier->name.' created', $supplier, [
                'name' => ['from' => null, 'to' => $supplier->name],
            ]);

            return $supplier;
        });
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            $supplier->fill($data)->save();
            $changes = $this->activity->changedFields($supplier);
            if ($changes !== []) {
                $this->activity->log('suppliers', 'updated', 'Supplier '.$supplier->name.' updated', $supplier, $changes);
            }

            return $supplier->refresh();
        });
    }

    public function delete(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier) {
            if ($supplier->expenses()->exists()) {
                throw ValidationException::withMessages([
                    'delete' => __('messages.supplier_has_expenses'),
                ]);
            }

            $supplier->delete();
            $this->activity->log('suppliers', 'deleted', 'Supplier '.$supplier->name.' deleted', $supplier);
        });
    }
}
