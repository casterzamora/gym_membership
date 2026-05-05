<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== API RESPONSE TEST ====\n\n";

// Create fresh admin token
$admin = \App\Models\User::where('role', 'admin')->first();
$token = $admin->createToken('api-token')->plainTextToken;
echo "Token: $token\n\n";

// Simulate API request
$classes = \App\Models\FitnessClass::with('trainer', 'schedules')
    ->withCount('attendances')
    ->orderByDesc('attendances_count')
    ->get()
    ->map(function ($class) {
        return [
            'id' => $class->id,
            'class_name' => $class->class_name,
            'description' => $class->description,
            'max_participants' => $class->max_participants,
            'difficulty_level' => $class->difficulty_level,
            'trainer_id' => $class->trainer_id,
            'current_enrolled' => $class->attendances_count,
            'remaining_slots' => max(0, $class->max_participants - $class->attendances_count),
            'is_full' => $class->attendances_count >= $class->max_participants,
            'enrollment_percentage' => round(($class->attendances_count / $class->max_participants) * 100),
            'trainer' => $class->trainer ? [
                'id' => $class->trainer->id,
                'first_name' => $class->trainer->first_name,
                'last_name' => $class->trainer->last_name,
                'specialization' => $class->trainer->specialization,
            ] : null,
            'schedules' => $class->schedules->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->class_date,
                'class_time' => $s->start_time,
                'duration' => $s->duration ?? 60,
            ]),
        ];
    })
    ->values();

echo "API Response (as returned by controller):\n";
$response = [
    'success' => true,
    'message' => 'Fitness classes retrieved successfully',
    'data' => $classes,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

echo "\n\n==== FRONTEND PARSING ====\n";
echo "response.data.data = " . (count($response['data']) > 0 ? "✓ Has data" : "✗ No data") . "\n";
echo "Number of classes: " . count($response['data']) . "\n";
echo "First class: " . json_encode($response['data'][0] ?? null, JSON_PRETTY_PRINT) . "\n";
