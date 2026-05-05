<?php
// Check if database connection works and admin user exists
$mysqli = new mysqli('127.0.0.1', 'root', '', 'gym_membership');

if ($mysqli->connect_error) {
    echo "Database connection failed: " . $mysqli->connect_error . "\n";
    exit(1);
}

echo "Database connection successful!\n\n";

// Check if users table exists
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✓ Users table exists\n";
} else {
    echo "✗ Users table does not exist - migrations haven't run\n";
    exit(1);
}

// Count users
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "  Total users: " . $row['count'] . "\n";

// Check for admin user
$result = $mysqli->query("SELECT * FROM users WHERE email = 'admin@gym.com'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "\n✓ Admin user found:\n";
    echo "  ID: " . $row['id'] . "\n";
    echo "  Name: " . $row['name'] . "\n";
    echo "  Email: " . $row['email'] . "\n";
    echo "  Role: " . ($row['role'] ?? 'N/A') . "\n";
    echo "  Password hash exists: " . (empty($row['password']) ? 'No' : 'Yes') . "\n";
} else {
    echo "\n✗ Admin user not found\n";
    echo "  Creating admin user...\n";
    
    // Create admin user
    $hashedPassword = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param('sssss', $name, $email, $hash, $role, $status);
    $name = 'Admin';
    $email = 'admin@gym.com';
    $hash = $hashedPassword;
    $role = 'admin';
    $status = 'active';
    
    if ($stmt->execute()) {
        echo "  ✓ Admin user created\n";
    } else {
        echo "  ✗ Failed to create admin user: " . $stmt->error . "\n";
    }
}

// Check membership plans
$result = $mysqli->query("SELECT COUNT(*) as count FROM membership_plans");
$row = $result->fetch_assoc();
echo "\nMembership plans: " . $row['count'] . "\n";

$mysqli->close();
