<?php
/**
 * Database Backup & Data Audit Script
 * Captures current state before consolidation refactoring
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');
$timestamp = date('Y-m-d_H-i-s');
$backupDir = __DIR__ . '/database_backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "=== DATABASE BACKUP & STATE AUDIT ===\n\n";

// 1. Export users table data
echo "[1] Exporting users table...\n";
$users = $pdo->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents("$backupDir/users_$timestamp.json", json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "  - " . count($users) . " users exported\n";

// 2. Export members table data
echo "[2] Exporting members table...\n";
$members = $pdo->query('SELECT * FROM members')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents("$backupDir/members_$timestamp.json", json_encode($members, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "  - " . count($members) . " members exported\n";

// 3. Export trainers table data
echo "[3] Exporting trainers table...\n";
$trainers = $pdo->query('SELECT * FROM trainers')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents("$backupDir/trainers_$timestamp.json", json_encode($trainers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "  - " . count($trainers) . " trainers exported\n";

// 4. Full MySQL dump
echo "[4] Creating full database backup...\n";
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'gym_membership';
$output = "$backupDir/gym_membership_$timestamp.sql";

$command = "mysqldump --user=$user --host=$host $db > $output 2>&1";
system($command);

if (file_exists($output)) {
    $size = filesize($output);
    echo "  - Full backup created: " . round($size / 1024 / 1024, 2) . " MB\n";
} else {
    echo "  - ERROR: Full backup failed\n";
}

// 5. Analyze member-user mapping
echo "\n[5] Analyzing member-user mappings...\n";
$memberEmailCount = $pdo->query("
    SELECT m.email, COUNT(*) as count
    FROM members m
    GROUP BY m.email
    HAVING count > 1
")->fetchAll(PDO::FETCH_ASSOC);

if (count($memberEmailCount) > 0) {
    echo "  - WARNING: Duplicate emails in members table: " . count($memberEmailCount) . "\n";
} else {
    echo "  - ✓ All member emails unique\n";
}

// Check if members exist with matching user emails
$memberUserMatches = $pdo->query("
    SELECT 
        m.id as member_id, 
        m.email as member_email,
        u.id as user_id, 
        u.email as user_email,
        u.role
    FROM members m
    LEFT JOIN users u ON u.email = m.email
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

echo "  - Member-user email matches:\n";
$matched = 0;
$unmatched = 0;
foreach ($memberUserMatches as $row) {
    if ($row['user_id']) {
        $matched++;
    } else {
        $unmatched++;
    }
}
echo "    - Matched: $matched\n";
echo "    - Unmatched (need new users): $unmatched\n";

// 6. Analyze trainer-user mapping
echo "\n[6] Analyzing trainer-user mappings...\n";
$trainerEmailCount = $pdo->query("
    SELECT t.email, COUNT(*) as count
    FROM trainers t
    GROUP BY t.email
    HAVING count > 1
")->fetchAll(PDO::FETCH_ASSOC);

if (count($trainerEmailCount) > 0) {
    echo "  - WARNING: Duplicate emails in trainers table: " . count($trainerEmailCount) . "\n";
} else {
    echo "  - ✓ All trainer emails unique\n";
}

// Check trainer-user matches
$trainerUserMatches = $pdo->query("
    SELECT 
        t.id as trainer_id, 
        t.email as trainer_email,
        u.id as user_id, 
        u.email as user_email,
        u.role
    FROM trainers t
    LEFT JOIN users u ON u.email = t.email
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

echo "  - Trainer-user email matches:\n";
$matched = 0;
$unmatched = 0;
foreach ($trainerUserMatches as $row) {
    if ($row['user_id']) {
        $matched++;
    } else {
        $unmatched++;
    }
}
echo "    - Matched: $matched\n";
echo "    - Unmatched (need new users): $unmatched\n";

// 7. Summary
echo "\n=== SUMMARY ===\n";
echo "Total Users: " . count($users) . "\n";
echo "Total Members: " . count($members) . "\n";
echo "Total Trainers: " . count($trainers) . "\n";
echo "Backup Location: $backupDir\n";
echo "Timestamp: $timestamp\n";
echo "\n✓ Backup and audit complete!\n";
