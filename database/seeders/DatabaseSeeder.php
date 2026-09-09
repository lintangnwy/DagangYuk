<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $adminRole      = Role::create(['name' => 'admin']);
        $cashierRole    = Role::create(['name' => 'user']);

        // Tenant
        $tenant = Tenant::create([
            'name'      => 'Kopi Senja',
            'address'   => 'Jl. Raya No. 123',
            'phone'     => '081234567890',
            'is_active' => true,
        ]);

        // Super Admin (tidak terikat tenant)
        User::create([
            'name'      => 'Super Admin',
            'email'     => 'superadmin@test.com',
            'password'  => Hash::make('password123'),
            'role_id'   => $superAdminRole->id,
            'tenant_id' => null,
        ]);

        // Admin toko
        User::create([
            'name'      => 'Admin Kopi Senja',
            'email'     => 'admin@test.com',
            'password'  => Hash::make('password123'),
            'role_id'   => $adminRole->id,
            'tenant_id' => $tenant->id,
        ]);

        // Kasir
        User::create([
            'name'      => 'Kasir Kopi Senja',
            'email'     => 'kasir@test.com',
            'password'  => Hash::make('password123'),
            'role_id'   => $cashierRole->id,
            'tenant_id' => $tenant->id,
        ]);
    }
}
