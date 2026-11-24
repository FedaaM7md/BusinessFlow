<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $policy = Password::min(10)->mixedCase()->numbers();
        $passwordRules = $user
            ? ['nullable', 'confirmed', $policy]
            : ['required', 'confirmed', $policy];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'locale' => ['required', 'in:ar,en'],
            'password' => $passwordRules,
            'is_active' => ['boolean'],
        ];
    }
}
