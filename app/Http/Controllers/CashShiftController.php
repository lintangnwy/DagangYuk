<?php

namespace App\Http\Controllers;

use App\Models\CashShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashShiftController extends Controller
{
    public function index()
    {
        $shifts = CashShift::with('user')
            ->latest()
            ->get();

        return response()->json($shifts);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'starting_cash' => 'required|numeric|min:0',
        ]);

        $existingShift = CashShift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'message' => 'Kamu masih memiliki shift yang terbuka'
            ], 422);
        }

        $shift = CashShift::create([
            'tenant_id' => $data['tenant_id'],
            'user_id' => Auth::id(),
            'starting_cash' => $data['starting_cash'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return response()->json([
            'message' => 'Shift berhasil dibuka',
            'data' => $shift
        ], 201);
    }

    public function show($id)
    {
        $shift = CashShift::with([
            'user',
            'orders'
        ])->findOrFail($id);

        return response()->json($shift);
    }

    public function close(Request $request, $id)
    {
        $shift = CashShift::findOrFail($id);

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
            'data' => $shift
        ]);
    }
}