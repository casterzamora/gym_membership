<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\FitnessClassController;
use App\Http\Controllers\Api\ClassScheduleController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\MembershipPlanController;
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

// API v1 Routes
Route::prefix('v1')->group(function () {

    // Protected Auth Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Membership Plans (protected write operations)
        Route::post('plans', [MembershipPlanController::class, 'store']);
        Route::get('plans/{plan}', [MembershipPlanController::class, 'show']);
        Route::put('plans/{plan}', [MembershipPlanController::class, 'update']);
        Route::delete('plans/{plan}', [MembershipPlanController::class, 'destroy']);

        // Members
        Route::apiResource('members', MemberController::class);

        // Trainers
        Route::apiResource('trainers', TrainerController::class);

        // Fitness Classes (protected write operations)
        Route::post('classes', [FitnessClassController::class, 'store']);
        Route::get('classes/{fitnessClass}', [FitnessClassController::class, 'show']);
        Route::put('classes/{fitnessClass}', [FitnessClassController::class, 'update']);
        Route::delete('classes/{fitnessClass}', [FitnessClassController::class, 'destroy']);

        // Class Schedules (protected write operations)
        Route::post('schedules', [ClassScheduleController::class, 'store']);
        Route::get('schedules/{schedule}', [ClassScheduleController::class, 'show']);
        Route::put('schedules/{schedule}', [ClassScheduleController::class, 'update']);
        Route::delete('schedules/{schedule}', [ClassScheduleController::class, 'destroy']);

        // Attendance
        Route::apiResource('attendance', AttendanceController::class);
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

        // Payments
        Route::apiResource('payments', PaymentController::class);

        // Equipment
        Route::apiResource('equipment', EquipmentController::class);
    });
});
