<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paymentsQuery = Payment::with('member', 'user', 'paymentMethod');

        // Filter by current user if they are a member
        $currentUser = $request->user();
        if ($currentUser && $currentUser->isMember()) {
            $memberProfile = $currentUser->member;
            if ($memberProfile) {
                $paymentsQuery->where('member_id', $memberProfile->id);
            }
        }

        $payments = $paymentsQuery->paginate(15);
        return $this->paginated($payments, 'Payments retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            $data = $request->validated();

            if ($this->hasCoverageOverlap(
                (int) $data['member_id'],
                Carbon::parse($data['coverage_start']),
                Carbon::parse($data['coverage_end'])
            )) {
                return $this->error('Payment coverage overlaps with an existing payment period', null, 422);
            }

            // Populate user_id from member's user_id
            if (isset($data['member_id'])) {
                $member = Member::find($data['member_id']);
                if ($member && $member->user_id) {
                    $data['user_id'] = $member->user_id;
                }
            }

            $payment = Payment::create($data);
            return $this->success($payment->load('member', 'user', 'paymentMethod'), 'Payment recorded successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to record payment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Payment $payment)
    {
        $currentUser = $request->user();
        
        // Check if user is a member and can only access their own payments
        if ($currentUser && $currentUser->isMember()) {
            $memberProfile = $currentUser->member;
            if ($memberProfile && (int) $payment->member_id !== (int) $memberProfile->id) {
                return $this->error('Forbidden: members can only access their own payments', null, 403);
            }
        }

        return $this->success($payment->load('member', 'user', 'paymentMethod'), 'Payment retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        try {
            $data = $request->validated();

            $memberId = (int) ($data['member_id'] ?? $payment->member_id);
            $coverageStart = Carbon::parse($data['coverage_start'] ?? $payment->coverage_start);
            $coverageEnd = Carbon::parse($data['coverage_end'] ?? $payment->coverage_end);

            if ($coverageEnd->lte($coverageStart)) {
                return $this->error('coverage_end must be after coverage_start', null, 422);
            }

            if ($this->hasCoverageOverlap($memberId, $coverageStart, $coverageEnd, (int) $payment->id)) {
                return $this->error('Payment coverage overlaps with an existing payment period', null, 422);
            }

            $payment->update($data);
            return $this->success($payment->load('member', 'user', 'paymentMethod'), 'Payment updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update payment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            $payment->delete();
            return $this->success(null, 'Payment deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete payment: ' . $e->getMessage(), null, 500);
        }
    }

    private function hasCoverageOverlap(int $memberId, Carbon $start, Carbon $end, ?int $excludePaymentId = null): bool
    {
        $query = Payment::query()
            ->where('member_id', $memberId)
            ->whereDate('coverage_start', '<=', $end->toDateString())
            ->whereDate('coverage_end', '>=', $start->toDateString());

        if ($excludePaymentId !== null) {
            $query->where('id', '!=', $excludePaymentId);
        }

        return $query->exists();
    }
}
