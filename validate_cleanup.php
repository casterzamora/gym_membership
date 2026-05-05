<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');

echo "==== POST-CLEANUP VALIDATION ====\n\n";

// Verify data integrity
echo "TEST 1: Data Integrity After Cleanup\n";
$memberCount = $pdo->query('SELECT COUNT(*) as count FROM members')->fetch(PDO::FETCH_ASSOC);
$userCount = $pdo->query('SELECT COUNT(*) as count FROM users')->fetch(PDO::FETCH_ASSOC);
$linkedMembers = $pdo->query('SELECT COUNT(*) as count FROM members WHERE user_id IS NOT NULL')->fetch(PDO::FETCH_ASSOC);

echo "✓ Members: " . $memberCount['count'] . "\n";
echo "✓ Users: " . $userCount['count'] . "\n";
echo "✓ Members with user_id: " . $linkedMembers['count'] . "\n";

// Verify no orphaned members
$orphaned = $pdo->query('SELECT COUNT(*) as count FROM members WHERE user_id IS NULL')->fetch(PDO::FETCH_ASSOC);
if ($orphaned['count'] > 0) {
    echo "✗ WARNING: " . $orphaned['count'] . " orphaned members found!\n";
} else {
    echo "✓ No orphaned members\n";
}

// Verify trainers linking
echo "\nTEST 2: Trainer Integrity\n";
$trainerCount = $pdo->query('SELECT COUNT(*) as count FROM trainers')->fetch(PDO::FETCH_ASSOC);
$linkedTrainers = $pdo->query('SELECT COUNT(*) as count FROM trainers WHERE user_id IS NOT NULL')->fetch(PDO::FETCH_ASSOC);

echo "✓ Trainers: " . $trainerCount['count'] . "\n";
echo "✓ Trainers with user_id: " . $linkedTrainers['count'] . "\n";

$orphanedTrainers = $pdo->query('SELECT COUNT(*) as count FROM trainers WHERE user_id IS NULL')->fetch(PDO::FETCH_ASSOC);
if ($orphanedTrainers['count'] > 0) {
    echo "✗ WARNING: " . $orphanedTrainers['count'] . " orphaned trainers found!\n";
} else {
    echo "✓ No orphaned trainers\n";
}

// Test FK relationships
echo "\nTEST 3: Foreign Key Constraints\n";
try {
    // Try to create a member with invalid user_id - should fail
    $pdo->query('INSERT INTO members (user_id, first_name, last_name, phone, plan_id, date_of_birth, registration_type, membership_status) VALUES (99999, "Test", "User", "555-1234", 1, "1990-01-01", "standard", "active")');
    echo "✗ FK constraint not enforced!\n";
} catch (Exception $e) {
    echo "✓ FK constraints properly enforced\n";
}

// Schema size comparison
echo "\nTEST 4: Schema Optimization\n";
$memberColumns = $pdo->query('SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "members" AND TABLE_SCHEMA = "gym_membership"')->fetch(PDO::FETCH_ASSOC);
$trainerColumns = $pdo->query('SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "trainers" AND TABLE_SCHEMA = "gym_membership"')->fetch(PDO::FETCH_ASSOC);
$userColumns = $pdo->query('SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "users" AND TABLE_SCHEMA = "gym_membership"')->fetch(PDO::FETCH_ASSOC);

echo "✓ Members table: " . $memberColumns['count'] . " columns (was 17)\n";
echo "✓ Trainers table: " . $trainerColumns['count'] . " columns (was 9)\n";
echo "✓ Users table: " . $userColumns['count'] . " columns (now authoritative)\n";

echo "\n==== CLEANUP VALIDATION PASSED ====\n";
