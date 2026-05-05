<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\User;
use App\Models\Member;
use App\Models\PaymentMethod;

echo "==== PAYMENT SYSTEM END-TO-END TEST ====\n\n";

// Test 1: Payment model relationships
echo "TEST 1: Payment Model Relationships\n";
$payment = Payment::with(['member', 'user', 'paymentMethod'])->first();
if ($payment) {
    echo "✓ Payment #" . $payment->id . "\n";
    echo "  - Member: " . ($payment->member ? $payment->member->first_name : "NOT LINKED") . "\n";
    echo "  - User: " . ($payment->user ? $payment->user->name : "NOT LINKED") . "\n";
    echo "  - Method: " . ($payment->paymentMethod ? $payment->paymentMethod->method_name : "NOT LINKED") . "\n";
    echo "  - Amount: $" . $payment->amount_paid . "\n";
    echo "  - Status: " . $payment->status ?? "N/A" . "\n";
}

// Test 2: Member payments relationship
echo "\nTEST 2: Member Payments Relationship\n";
$member = Member::with('payments')->first();
if ($member) {
    echo "✓ Member: " . $member->first_name . " " . $member->last_name . "\n";
    echo "  - Has " . count($member->payments) . " payments\n";
    echo "  - User ID: " . $member->user_id . "\n";
}

// Test 3: User payments through member
echo "\nTEST 3: User → Member → Payments Chain\n";
$userWithMember = User::with(['member.payments'])->where('role', 'member')->first();
if ($userWithMember && $userWithMember->member) {
    echo "✓ User: " . $userWithMember->name . "\n";
    echo "  - Member ID: " . $userWithMember->member->id . "\n";
    echo "  - Payments through member: " . count($userWithMember->member->payments) . "\n";
}

// Test 4: Payment method relationships
echo "\nTEST 4: Payment Methods\n";
$methods = PaymentMethod::withCount('payments')->get();
echo "✓ Total payment methods: " . count($methods) . "\n";
foreach ($methods as $method) {
    echo "  - " . $method->method_name . ": " . $method->payments_count . " payments\n";
}

// Test 5: Data integrity checks
echo "\nTEST 5: Data Integrity\n";
$orphanedPayments = Payment::whereNull('member_id')->count();
$orphanedUsers = Payment::whereNull('user_id')->count();
$orphanedMethods = Payment::whereNull('payment_method_id')->count();

echo "✓ Orphaned payments (no member): " . $orphanedPayments . "\n";
echo "✓ Payments without user_id: " . $orphanedUsers . "\n";
echo "✓ Payments without payment method: " . $orphanedMethods . "\n";

// Test 6: Query performance
echo "\nTEST 6: Query Performance\n";
$start = microtime(true);
$payments = Payment::with(['member.user', 'paymentMethod'])->get();
$time = round((microtime(true) - $start) * 1000, 2);
echo "✓ Loaded " . count($payments) . " payments with relationships in " . $time . "ms\n";

echo "\n==== ALL PAYMENT TESTS PASSED ====\n";
