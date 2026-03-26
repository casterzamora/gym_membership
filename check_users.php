#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Http\Kernel::class);

$users = \App\Models\User::select('id', 'email', 'role')->orderBy('id')->get();

echo "\n=== USERS IN DATABASE ===\n\n";

foreach ($users as $user) {
    echo "ID: " . $user->id 
        . " | Email: " . $user->email 
        . " | Role: " . ($user->role ?? 'NULL') 
        . "\n";
}

echo "\n=== ADMIN USER CHECK ===\n";
$admin = \App\Models\User::where('email', 'admin@gym.com')->first();
if ($admin) {
    echo "Admin user found: " . $admin->email . " | Role: " . ($admin->role ?? 'NULL') . "\n";
} else {
    echo "Admin user NOT found\n";
}

echo "\n";
