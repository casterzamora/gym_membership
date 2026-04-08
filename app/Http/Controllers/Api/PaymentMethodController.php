<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Traits\ApiResponse;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of payment methods.
     */
    public function index()
    {
        $methods = PaymentMethod::paginate(15);
        return $this->paginated($methods, 'Payment methods retrieved successfully');
    }

    /**
     * Display the specified payment method.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return $this->success($paymentMethod->load('payments'), 'Payment method retrieved successfully');
    }
}
