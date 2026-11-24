<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\CRM\Services;

use App\Modules\Core\Services\ActivityLogger;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Customer;
use Illuminate\Support\Facades\DB;

class ContactService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function create(Customer $customer, array $data): Contact
    {
        return DB::transaction(function () use ($customer, $data) {
            if (! empty($data['is_primary'])) {
                $customer->contacts()->update(['is_primary' => false]);
            }

            $contact = $customer->contacts()->create($data);
            $this->activity->log('crm', 'created', 'Contact '.$contact->name.' added to '.$customer->name, $contact);

            return $contact;
        });
    }

    public function update(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            if (! empty($data['is_primary'])) {
                $contact->customer->contacts()->whereKeyNot($contact->id)->update(['is_primary' => false]);
            }

            $contact->fill($data)->save();
            $this->activity->log('crm', 'updated', 'Contact '.$contact->name.' updated', $contact, $this->activity->changedFields($contact));

            return $contact->refresh();
        });
    }

    public function delete(Contact $contact): void
    {
        DB::transaction(function () use ($contact) {
            $name = $contact->name;
            $contact->delete();
            $this->activity->log('crm', 'deleted', 'Contact '.$name.' deleted', $contact);
        });
    }
}
