<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Build a base query scoped to the current user's tenant.
     * Super Admin (role_id = 1) sees all users.
     * Tenant Admin sees only users within their own tenant (excluding super_admin).
     */
    private function scopedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $authUser = auth()->user();

        $query = User::with(['role', 'tenant']);

        // Super Admin → no filter
        if ($authUser->role_id === 1) {
            return $query;
        }

        // Tenant user → only same tenant, exclude super_admin (role_id=1)
        return $query
            ->where('tenant_id', $authUser->tenant_id)
            ->where('role_id', '!=', 1);
    }

    public function index()
    {
        $users = $this->scopedQuery()->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();

        $data = $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'role_id'   => 'required|exists:roles,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
        ]);

        // Tenant admin: force tenant_id to own, and restrict role to admin(2) or kasir(3)
        if ($authUser->role_id !== 1) {
            $data['tenant_id'] = $authUser->tenant_id;
            if (!in_array((int) $data['role_id'], [2, 3])) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk membuat user dengan role ini.'
                ], 403);
            }
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data'    => $user->load(['role', 'tenant']),
        ], 201);
    }

    public function show($id)
    {
        $user = $this->scopedQuery()->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $authUser = auth()->user();
        $user = $this->scopedQuery()->findOrFail($id);

        $data = $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'role_id'   => 'required|exists:roles,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:6',
        ]);

        // Tenant admin: lock tenant_id, restrict role
        if ($authUser->role_id !== 1) {
            $data['tenant_id'] = $authUser->tenant_id;
            if (!in_array((int) $data['role_id'], [2, 3])) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk mengubah role ini.'
                ], 403);
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User berhasil diubah',
            'data'    => $user->load(['role', 'tenant']),
        ]);
    }

    public function destroy($id)
    {
        $authUser = auth()->user();
        $user = $this->scopedQuery()->findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === $authUser->id) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun sendiri.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }
}