<?php

require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->boot();

use App\Models\FitnessClass;
use App\Models\User;

// Get a test admin user
$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "❌ No admin user found\n";
    exit(1);
}

// Get classes before delete
$classesBefore = FitnessClass::count();
echo "📊 Classes before: " . $classesBefore . "\n";

// Get a class to delete
$classToDelete = FitnessClass::first();
if (!$classToDelete) {
    echo "❌ No classes found to delete\n";
    exit(1);
}

echo "🎯 Deleting class ID: " . $classToDelete->id . " - " . $classToDelete->class_name . "\n";

try {
    $classToDelete->delete();
    echo "✅ Delete executed successfully\n";
} catch (\Exception $e) {
    echo "❌ Delete error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if actually deleted
$classesAfter = FitnessClass::count();
echo "📊 Classes after: " . $classesAfter . "\n";

if ($classesAfter < $classesBefore) {
    echo "✅ Delete WORKED - Class removed from database\n";
} else {
    echo "❌ Delete FAILED - Class still in database\n";
    // Check if it still exists
    $stillExists = FitnessClass::find($classToDelete->id);
    if ($stillExists) {
        echo "   Class still exists: ID " . $stillExists->id . " - " . $stillExists->class_name . "\n";
    }
}
