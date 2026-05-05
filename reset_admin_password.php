<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('role', 'admin')->first();

// Set password to 'admin123'
$admin->password = Hash::make('admin123');
$admin->save();

echo "✓ Admin password reset to 'admin123'\n";
echo "  Email: {$admin->email}\n";
echo "  Password hash updated\n";
