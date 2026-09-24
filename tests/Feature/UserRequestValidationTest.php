<?php

namespace Tests\Feature;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Tests\TestCase;

class UserRequestValidationTest extends TestCase
{
    public function test_store_user_request_has_expected_rules(): void
    {
        $request = new StoreUserRequest();

        $this->assertSame([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'role_id'   => ['required', 'exists:roles,id'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6'],
        ], $request->rules());
    }

    public function test_update_user_request_has_expected_rules(): void
    {
        $request = new UpdateUserRequest();

        $this->assertSame([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'role_id'   => ['required', 'exists:roles,id'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,' . ($request->route('user') ?? '0')],
            'password'  => ['nullable', 'string', 'min:6'],
        ], $request->rules());
    }

}
