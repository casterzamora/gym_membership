<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\FitnessClassController;
use App\Http\Controllers\Api\ClassScheduleController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\MembershipPlanController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\HealthCheckController;

// Health Check
Route::get('health', [HealthCheckController::class, 'check']);

// Public Auth Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Public Read-Only Routes (for browsing)
Route::get('v1/plans', [MembershipPlanController::class, 'index']);
Route::get('v1/classes', [FitnessClassController::class, 'index']);
Route::get('v1/schedules', [ClassScheduleController::class, 'index']);
Route::get('v1/payment-methods', [PaymentMethodController::class, 'index']);

// API v1 Routes
Route::prefix('v1')->group(function () {

    // Protected Auth Routes
    Route::middleware('dual.auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Membership Plans (protected write operations)
        Route::post('plans', [MembershipPlanController::class, 'store'])->middleware('role:admin');
        Route::get('plans/{plan}', [MembershipPlanController::class, 'show']);
        Route::put('plans/{plan}', [MembershipPlanController::class, 'update'])->middleware('role:admin');
        Route::delete('plans/{plan}', [MembershipPlanController::class, 'destroy'])->middleware('role:admin');

        // Members
        Route::get('members', [MemberController::class, 'index'])->middleware('role:admin,trainer');
        Route::post('members', [MemberController::class, 'store'])->middleware('role:admin');
        Route::get('members/{member}', [MemberController::class, 'show'])
            ->middleware(['role:admin,trainer,member', 'member.self']);
        Route::put('members/{member}', [MemberController::class, 'update'])
            ->middleware(['role:admin,member', 'member.self']);
        Route::patch('members/{member}', [MemberController::class, 'update'])
            ->middleware(['role:admin,member', 'member.self']);
        Route::post('members/{member}/renew', [MemberController::class, 'renew'])
            ->middleware(['role:admin,member', 'member.self']);
        Route::post('members/{member}/upgrade', [MemberController::class, 'upgrade'])
            ->middleware(['role:admin,member', 'member.self']);
        Route::delete('members/{member}', [MemberController::class, 'destroy'])->middleware('role:admin');

        // Trainers
        Route::get('trainers/workload-summary', [TrainerController::class, 'workloadSummary'])->middleware('role:admin');
        Route::get('trainers/{trainer}/workload', [TrainerController::class, 'workload'])->middleware('role:admin,trainer');
        Route::apiResource('trainers', TrainerController::class)->middleware('role:admin')->except(['index', 'show']);

        // Fitness Classes (protected write operations)
        Route::get('classes', [FitnessClassController::class, 'index']);
        Route::post('classes', [FitnessClassController::class, 'store'])->middleware('role:admin,trainer');
        Route::get('classes/{fitnessClass}', [FitnessClassController::class, 'show']);
        Route::put('classes/{fitnessClass}', [FitnessClassController::class, 'update'])->middleware('role:admin,trainer');
        Route::patch('classes/{fitnessClass}', [FitnessClassController::class, 'update'])->middleware('role:admin,trainer');
        Route::delete('classes/{fitnessClass}', [FitnessClassController::class, 'destroy'])->middleware('role:admin,trainer');

        // Class Schedules (protected write operations)
        Route::post('schedules', [ClassScheduleController::class, 'store'])->middleware('role:admin,trainer');
        Route::get('schedules/{schedule}', [ClassScheduleController::class, 'show']);
        Route::put('schedules/{schedule}', [ClassScheduleController::class, 'update'])->middleware('role:admin,trainer');
        Route::delete('schedules/{schedule}', [ClassScheduleController::class, 'destroy'])->middleware('role:admin,trainer');

        // Attendance
        Route::get('attendance', [AttendanceController::class, 'index'])->middleware('role:admin,trainer,member');
        Route::post('attendance', [AttendanceController::class, 'store'])->middleware('role:admin,trainer,member');
        Route::get('attendance/{member_id}/{schedule_id}', [AttendanceController::class, 'show'])->middleware('role:admin,trainer,member');
        Route::put('attendance/{member_id}/{schedule_id}', [AttendanceController::class, 'update'])->middleware('role:admin,trainer,member');
        Route::patch('attendance/{member_id}/{schedule_id}', [AttendanceController::class, 'update'])->middleware('role:admin,trainer,member');
        Route::delete('attendance/{member_id}/{schedule_id}', [AttendanceController::class, 'destroy'])->middleware('role:admin,trainer');
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->middleware('role:admin,trainer,member');
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->middleware('role:admin,trainer,member');

        // Payments
        Route::get('payments', [PaymentController::class, 'index'])->middleware('role:admin,member');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->middleware('role:admin,member');
        Route::post('payments', [PaymentController::class, 'store'])->middleware('role:admin');
        Route::put('payments/{payment}', [PaymentController::class, 'update'])->middleware('role:admin');
        Route::patch('payments/{payment}', [PaymentController::class, 'update'])->middleware('role:admin');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->middleware('role:admin');

        // Payment Methods
        Route::get('payment-methods', [PaymentMethodController::class, 'index']);
        Route::get('payment-methods/{paymentMethod}', [PaymentMethodController::class, 'show']);

        // Equipment
        Route::apiResource('equipment', EquipmentController::class)->middleware('role:admin')->except(['index', 'show']);

        // Reports
        Route::get('reports/revenue', [ReportController::class, 'revenue'])->middleware('role:admin');
        Route::get('reports/class-popularity', [ReportController::class, 'classPopularity'])->middleware('role:admin');
        Route::get('reports/low-attendance-members', [ReportController::class, 'lowAttendanceMembers'])->middleware('role:admin');
    });
});
