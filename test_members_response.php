<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Member;

echo "=== Members API Response Test ===\n\n";

try {
    $members = Member::with('plan', 'attendances')->get();
    
    echo "Members found: " . $members->count() . "\n\n";
    
    if ($members->count() > 0) {
        echo "Sample Member Data:\n";
        $sample = $members->first();
        
        $data = [
            'id' => $sample->id,
            'first_name' => $sample->first_name,
            'last_name' => $sample->last_name,
            'email' => $sample->email,
            'phone' => $sample->phone,
            'plan' => [
                'id' => $sample->plan?->id,
                'plan_name' => $sample->plan?->plan_name,
            ],
            'attendances' => $sample->attendances ? $sample->attendances->count() : 0,
        ];
        
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        
        echo "API will return " . $members->count() . " members for the trainer to display.\n";
        echo "✅ Students section should show this data.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
