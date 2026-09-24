<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id') ?? null;

        return [
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'role_id'   => ['required', 'exists:roles,id'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,' . $userId],
            'password'  => ['nullable', 'string', 'min:6'],
        ];
    }
}
