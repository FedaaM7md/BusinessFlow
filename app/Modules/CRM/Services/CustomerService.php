<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\CRM\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\CRM\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(array $data, User $actor): Customer
    {
        return DB::transaction(function () use ($data, $actor) {
            $data['created_by'] = $actor->id;
            $data['code'] = $this->nextCode();

            $customer = Customer::create($data);

            $this->activity->log(
                'crm',
                'created',
                'Customer '.$customer->name.' created',
                $customer,
                ['name' => ['from' => null, 'to' => $customer->name]],
            );

            return $customer;
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->fill($data)->save();
            $changes = $this->activity->changedFields($customer);

            if ($changes !== []) {
                $this->activity->log('crm', 'updated', 'Customer '.$customer->name.' updated', $customer, $changes);
            }

            return $customer->refresh();
        });
    }

    public function delete(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            if ($customer->invoices()->exists() || $customer->quotations()->exists() || $customer->payments()->exists()) {
                throw ValidationException::withMessages([
                    'delete' => __('messages.customer_has_documents'),
                ]);
            }

            $customer->delete();
            $this->activity->log('crm', 'deleted', 'Customer '.$customer->name.' deleted', $customer);
        });
    }

    private function nextCode(): string
    {
        $next = ((int) Customer::withTrashed()->max('id')) + 1;

        return 'CUS-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
