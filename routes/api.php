<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentController;

// Members
Route::prefix('members')->group(function () {
    Route::get('/', [MemberController::class, 'index']);
    Route::post('/', [MemberController::class, 'store']);
    Route::get('/{member}', [MemberController::class, 'show']);
    Route::put('/{member}', [MemberController::class, 'update']);
    Route::delete('/{member}', [MemberController::class, 'destroy']);
    Route::get('/status/{status}', [MemberController::class, 'getByStatus']);
});

// Attendance
Route::prefix('attendance')->group(function () {
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/today', [AttendanceController::class, 'getTodayAttendance']);
    Route::get('/member/{member}', [AttendanceController::class, 'getMemberAttendance']);
    Route::get('/stats', [AttendanceController::class, 'getAttendanceStats']);
});

// Memberships
Route::prefix('memberships')->group(function () {
    Route::get('/', [MembershipController::class, 'index']);
    Route::post('/', [MembershipController::class, 'store']);
    Route::get('/{membership}', [MembershipController::class, 'show']);
    Route::post('/{membership}/renew', [MembershipController::class, 'renew']);
    Route::post('/{membership}/cancel', [MembershipController::class, 'cancel']);
    Route::get('/active/all', [MembershipController::class, 'getActiveMemberships']);
    Route::get('/expiring/soon', [MembershipController::class, 'getExpiringMemberships']);
});

// Payments
Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/member/{member}', [PaymentController::class, 'getMemberPayments']);
    Route::get('/date-range', [PaymentController::class, 'getPaymentsByDateRange']);
    Route::get('/stats', [PaymentController::class, 'getPaymentStats']);
});
