<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Member;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::with('member', 'membership')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'membership_id' => 'required|exists:memberships,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer',
            'transaction_id' => 'nullable|unique:payments',
        ]);

        $payment = Payment::create([
            ...$validated,
            'payment_date' => today(),
            'status' => 'completed',
        ]);

        return response()->json($payment, 201);
    }

    public function getMemberPayments(Member $member)
    {
        return Payment::where('member_id', $member->id)
            ->with('membership')
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getPaymentsByDateRange(Request $request)
    {
        $fromDate = $request->query('from_date', now()->subMonths(1)->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        return Payment::whereBetween('payment_date', [$fromDate, $toDate])
            ->with('member', 'membership')
            ->get();
    }

    public function getPaymentStats(Request $request)
    {
        $fromDate = $request->query('from_date', now()->subMonths(1)->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        $totalRevenue = Payment::whereBetween('payment_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->sum('amount');

        $paymentsByMethod = Payment::whereBetween('payment_date', [$fromDate, $toDate])
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->get();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'payments_by_method' => $paymentsByMethod,
        ]);
    }
}
