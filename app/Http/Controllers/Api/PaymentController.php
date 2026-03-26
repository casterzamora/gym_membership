<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Traits\ApiResponse;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with('member')->paginate(15);
        return $this->paginated($payments, 'Payments retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        try {
            $payment = Payment::create($request->validated());
            return $this->success($payment->load('member'), 'Payment recorded successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to record payment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        return $this->success($payment->load('member'), 'Payment retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        try {
            $payment->update($request->validated());
            return $this->success($payment->load('member'), 'Payment updated successfully');
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
}
