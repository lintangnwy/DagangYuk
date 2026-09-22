<?php

namespace Tests\Feature;

use App\Models\CashShift;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashShiftAndOrderBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_shift_uses_current_tenant_and_prevents_multiple_open_shifts(): void
    {
        $tenant = Tenant::create([
            'name' => 'Toko A',
            'address' => 'Jl. Toko A',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'user']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'tenant_id' => $tenant->id,
        ]);

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/cash-shifts', [
            'starting_cash' => 100000,
        ])->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.user_id', $user->id);

        $this->postJson('/api/cash-shifts', [
            'starting_cash' => 200000,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Kamu masih memiliki shift yang terbuka');
    }

    public function test_order_rejects_shift_from_another_tenant_or_closed_shift(): void
    {
        $tenantA = Tenant::create([
            'name' => 'Toko A',
            'address' => 'Jl. Toko A',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $tenantB = Tenant::create([
            'name' => 'Toko B',
            'address' => 'Jl. Toko B',
            'phone' => '0822222222',
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'user']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'tenant_id' => $tenantA->id,
        ]);

        $otherUser = User::factory()->create([
            'role_id' => $role->id,
            'tenant_id' => $tenantB->id,
        ]);

        $category = Category::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Minuman',
        ]);

        $product = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $category->id,
            'name' => 'Es Kopi',
            'price' => 15000,
            'cost_price' => 7000,
            'stock' => 20,
            'sku' => 'SKU-001',
        ]);

        $shiftA = CashShift::create([
            'tenant_id' => $tenantA->id,
            'user_id' => $user->id,
            'starting_cash' => 100000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $shiftB = CashShift::create([
            'tenant_id' => $tenantB->id,
            'user_id' => $otherUser->id,
            'starting_cash' => 250000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/orders', [
            'cash_shift_id' => $shiftA->id,
            'payment_method' => 'cash',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertStatus(201);

        $this->postJson('/api/orders', [
            'cash_shift_id' => $shiftB->id,
            'payment_method' => 'cash',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Shift kasir tidak valid untuk tenant Anda.');

        $shiftA->update(['status' => 'closed', 'closed_at' => now()]);

        $this->postJson('/api/orders', [
            'cash_shift_id' => $shiftA->id,
            'payment_method' => 'cash',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Shift kasir sudah ditutup.');
    }
}
