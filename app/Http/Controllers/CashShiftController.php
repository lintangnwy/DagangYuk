<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashShiftController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = CashShift::with('user');

        if ($user && $user->role_id !== 1) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $shifts = $query->latest()->get();

        return response()->json($shifts);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->tenant_id) {
            return response()->json([
                'message' => 'Anda harus memiliki tenant aktif untuk membuka shift.'
            ], 403);
        }

        $data = $request->validate([
            'starting_cash' => 'required|numeric|min:0',
        ]);

        $existingShift = CashShift::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'message' => 'Kamu masih memiliki shift yang terbuka'
            ], 422);
        }

        $shift = CashShift::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'starting_cash' => $data['starting_cash'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return response()->json([
            'message' => 'Shift berhasil dibuka',
            'data' => $shift->load('user', 'tenant')
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $query = CashShift::with(['user', 'orders']);

        if ($user && $user->role_id !== 1) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $shift = $query->findOrFail($id);

        return response()->json($shift);
    }

    public function close(Request $request, $id)
    {
        $user = Auth::user();
        $query = CashShift::query();

        if ($user && $user->role_id !== 1) {
            $query->where('tenant_id', $user->tenant_id)
                ->where('user_id', $user->id);
        }

        $shift = $query->findOrFail($id);

        if ($shift->status === 'closed') {
            return response()->json([
                'message' => 'Shift sudah ditutup'
            ], 422);
        }

        $data = $request->validate([
            'ending_cash' => 'required|numeric|min:0',
        ]);

        $cashSales = $shift->orders()
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $expectedCash = $shift->starting_cash + $cashSales;

        $shift->update([
            'ending_cash' => $data['ending_cash'],
            'expected_cash' => $expectedCash,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Shift berhasil ditutup',
            'data' => $shift->fresh()
        ]);
    }
}