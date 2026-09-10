<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $adminRole      = Role::create(['name' => 'admin']);
        $cashierRole    = Role::create(['name' => 'user']);

        // ── Tenant ─────────────────────────────────────
        $tenant = Tenant::create([
            'name'      => 'Kopi Senja',
            'address'   => 'Jl. Raya No. 123',
            'phone'     => '081234567890',
            'is_active' => true,
        ]);

        // ── Users ──────────────────────────────────────
        User::create([
            'name'      => 'Super Admin',
            'email'     => 'superadmin@test.com',
            'password'  => Hash::make('password123'),
            'role_id'   => $superAdminRole->id,
            'tenant_id' => null,
        ]);

        User::create([
            'name'      => 'Admin Kopi Senja',
            'email'     => 'admin@test.com',
            'password'  => Hash::make('password123'),
            'role_id'   => $adminRole->id,
            'tenant_id' => $tenant->id,
        ]);

        User::create([
            'name'      => 'Kasir Kopi Senja',
            'email'     => 'kasir@test.com',
            'password'  => Hash::make('password123'),
            'role_id'   => $cashierRole->id,
            'tenant_id' => $tenant->id,
        ]);

        // ── Kategori ───────────────────────────────────
        $catMinuman = Category::create(['tenant_id' => $tenant->id, 'name' => 'Minuman']);
        $catMakanan = Category::create(['tenant_id' => $tenant->id, 'name' => 'Makanan']);
        $catSnack   = Category::create(['tenant_id' => $tenant->id, 'name' => 'Snack']);

        // ── Produk ─────────────────────────────────────
        $products = [
            // Minuman
            ['name' => 'Es Kopi Susu',       'price' => 18000, 'cost_price' => 8000,  'stock' => 50, 'category_id' => $catMinuman->id],
            ['name' => 'Es Matcha Latte',     'price' => 22000, 'cost_price' => 10000, 'stock' => 40, 'category_id' => $catMinuman->id],
            ['name' => 'Kopi Americano',      'price' => 15000, 'cost_price' => 6000,  'stock' => 60, 'category_id' => $catMinuman->id],
            ['name' => 'Es Teh Manis',        'price' => 8000,  'cost_price' => 2000,  'stock' => 80, 'category_id' => $catMinuman->id],
            ['name' => 'Jus Alpukat',         'price' => 20000, 'cost_price' => 9000,  'stock' => 30, 'category_id' => $catMinuman->id],

            // Makanan
            ['name' => 'Nasi Goreng Spesial', 'price' => 25000, 'cost_price' => 12000, 'stock' => 20, 'category_id' => $catMakanan->id],
            ['name' => 'Mie Goreng',          'price' => 20000, 'cost_price' => 9000,  'stock' => 20, 'category_id' => $catMakanan->id],
            ['name' => 'Roti Bakar',          'price' => 15000, 'cost_price' => 6000,  'stock' => 25, 'category_id' => $catMakanan->id],
            ['name' => 'Sandwich',            'price' => 22000, 'cost_price' => 10000, 'stock' => 15, 'category_id' => $catMakanan->id],

            // Snack
            ['name' => 'Kentang Goreng',      'price' => 18000, 'cost_price' => 7000,  'stock' => 30, 'category_id' => $catSnack->id],
            ['name' => 'Pisang Goreng',       'price' => 12000, 'cost_price' => 4000,  'stock' => 25, 'category_id' => $catSnack->id],
            ['name' => 'Donat',               'price' => 8000,  'cost_price' => 3000,  'stock' => 40, 'category_id' => $catSnack->id],
        ];

        foreach ($products as $p) {
            Product::create([
                'tenant_id'   => $tenant->id,
                'category_id' => $p['category_id'],
                'name'        => $p['name'],
                'price'       => $p['price'],
                'cost_price'  => $p['cost_price'],
                'stock'       => $p['stock'],
                'sku'         => null,
                'image'       => null,
            ]);
        }
    }
}
