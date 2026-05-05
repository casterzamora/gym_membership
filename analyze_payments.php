<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');

echo "==== PAYMENTS TABLE ANALYSIS ====\n\n";

// Get full schema
echo "PAYMENTS TABLE STRUCTURE:\n";
$stmt = $pdo->query('DESCRIBE payments');
$columns = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")" . ($row['Key'] ? " - " . $row['Key'] : "") . "\n";
    $columns[] = $row['Field'];
}

// Get sample data
echo "\nSAMPLE PAYMENT RECORDS:\n";
try {
    $stmt = $pdo->query('SELECT id, member_id, amount, amount_paid, payment_method_id, payment_method, status, created_at FROM payments LIMIT 3');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Payment #" . $row['id'] . ":\n";
        echo "  - Member ID: " . $row['member_id'] . "\n";
        echo "  - Amount: " . $row['amount'] . "\n";
        echo "  - Amount Paid: " . $row['amount_paid'] . "\n";
        echo "  - Payment Method ID: " . $row['payment_method_id'] . "\n";
        echo "  - Payment Method (string): " . $row['payment_method'] . "\n";
        echo "  - Status: " . $row['status'] . "\n";
        echo "  - Created: " . $row['created_at'] . "\n\n";
    }
} catch (Exception $e) {
    echo "Error querying payments: " . $e->getMessage() . "\n";
}

// Check for denormalization issues
echo "DENORMALIZATION ANALYSIS:\n";

// Issue 1: amount vs amount_paid
try {
    $stmt = $pdo->query('SELECT COUNT(*) as mismatch FROM payments WHERE amount != amount_paid');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "1. Amount mismatches (amount ≠ amount_paid): " . $result['mismatch'] . " records\n";
} catch (Exception $e) {
    echo "1. Error checking amount: " . $e->getMessage() . "\n";
}

// Issue 2: payment_method_id vs payment_method string
echo "2. Payment Method Duplication:\n";
try {
    $stmt = $pdo->query('SELECT payment_method_id, payment_method, COUNT(*) as count FROM payments GROUP BY payment_method_id, payment_method');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - ID: " . $row['payment_method_id'] . " | String: " . $row['payment_method'] . " | Count: " . $row['count'] . "\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Issue 3: booking_id reference
echo "3. Booking ID Reference Check:\n";
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM payments');
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Total payments: " . $total['count'] . "\n";

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM payments WHERE booking_id IS NULL');
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Payments with NULL booking_id: " . $nullCount['count'] . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Check if bookings table exists
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM bookings');
    $bookCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Bookings table exists with " . $bookCount['count'] . " records\n";
} catch (Exception $e) {
    echo "   - ✗ Bookings table does NOT exist (FK reference is orphaned)\n";
}

// Issue 4: Multiple date fields
echo "4. Date Field Analysis:\n";
try {
    $stmt = $pdo->query('SELECT 
        COUNT(*) as total,
        COUNT(DISTINCT DATE(payment_date)) as unique_payment_dates,
        COUNT(DISTINCT DATE(coverage_start)) as unique_coverage_starts,
        COUNT(DISTINCT DATE(coverage_end)) as unique_coverage_ends
    FROM payments');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Total payments: " . $result['total'] . "\n";
    echo "   - Unique payment dates: " . $result['unique_payment_dates'] . "\n";
    echo "   - Unique coverage starts: " . $result['unique_coverage_starts'] . "\n";
    echo "   - Unique coverage ends: " . $result['unique_coverage_ends'] . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Payment method reference integrity
echo "5. Payment Method Reference Check:\n";
try {
    $stmt = $pdo->query('
        SELECT COUNT(*) as orphaned 
        FROM payments p 
        LEFT JOIN payment_methods pm ON p.payment_method_id = pm.payment_method_id 
        WHERE pm.payment_method_id IS NULL AND p.payment_method_id IS NOT NULL
    ');
    $orphaned = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - Orphaned payment method references: " . $orphaned['orphaned'] . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n==== ANALYSIS COMPLETE ====\n";
