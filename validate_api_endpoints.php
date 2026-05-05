<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

echo "==== API ENDPOINT VALIDATION ====\n\n";

// Simulate API requests to verify routes exist
$routes = Route::getRoutes();
$apiRoutes = collect($routes)->filter(function($route) {
    return strpos($route->uri(), 'api/') === 0;
})->groupBy(function($route) {
    $uri = $route->uri();
    $parts = explode('/', $uri);
    return $parts[1] ?? 'unknown';
})->toArray();

echo "API Routes by Resource:\n";
foreach ($apiRoutes as $resource => $routes) {
    echo "  $resource:\n";
    foreach ($routes as $route) {
        $methods = implode('|', $route->methods());
        echo "    - " . str_pad($methods, 8) . " " . $route->uri() . "\n";
    }
}

// Check key endpoints exist
echo "\nKey Endpoints Check:\n";
$criticalRoutes = [
    'POST api/auth/register' => false,
    'POST api/auth/login' => false,
    'GET api/members' => false,
    'POST api/members' => false,
    'GET api/trainers' => false,
    'GET api/payments' => false,
    'GET api/equipment' => false,
    'GET api/fitness-classes' => false,
];

foreach ($apiRoutes as $routeList) {
    foreach ($routeList as $route) {
        foreach ($criticalRoutes as $check => &$found) {
            list($method, $uri) = explode(' ', $check);
            if (!$found && in_array($method, $route->methods()) && $route->uri() === $uri) {
                $found = true;
            }
        }
    }
}

foreach ($criticalRoutes as $route => $found) {
    echo ($found ? "✓" : "✗") . " $route\n";
}

echo "\n==== MODEL RELATIONSHIPS VALIDATION ====\n\n";

// Verify models have correct relationships
$userModel = \App\Models\User::class;
$memberModel = \App\Models\Member::class;
$trainerModel = \App\Models\Trainer::class;

echo "User Model:\n";
$userMethods = get_class_methods($userModel);
echo "  Methods: " . count($userMethods) . "\n";
echo "  - member() relationship: " . (in_array('member', $userMethods) ? "✓" : "✗") . "\n";
echo "  - trainer() relationship: " . (in_array('trainer', $userMethods) ? "✓" : "✗") . "\n";
echo "  - isMember() helper: " . (in_array('isMember', $userMethods) ? "✓" : "✗") . "\n";
echo "  - isTrainer() helper: " . (in_array('isTrainer', $userMethods) ? "✓" : "✗") . "\n";

echo "\nMember Model:\n";
$memberMethods = get_class_methods($memberModel);
echo "  - user() relationship: " . (in_array('user', $memberMethods) ? "✓" : "✗") . "\n";
echo "  - payments() relationship: " . (in_array('payments', $memberMethods) ? "✓" : "✗") . "\n";

echo "\nTrainer Model:\n";
$trainerMethods = get_class_methods($trainerModel);
echo "  - user() relationship: " . (in_array('user', $trainerMethods) ? "✓" : "✗") . "\n";
echo "  - classes() relationship: " . (in_array('classes', $trainerMethods) ? "✓" : "✗") . "\n";

echo "\n==== API VALIDATION COMPLETE ====\n";
