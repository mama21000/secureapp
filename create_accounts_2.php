<?php
declare(strict_types=1);

// Run this script from the terminal: php create_accounts.php users.csv
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

require_once __DIR__ . '/app/Core/config.php';
require_once __DIR__ . '/app/Core/db.php';

use App\Core\DB;

echo "========================================\n";
echo "🛡️ SecureApp Auto-Account Generator 🛡️\n";
echo "========================================\n";

// Expect the CSV file path as the first argument
if ($argc < 2) {
    echo "[!] Usage: php create_accounts.php <path-to-csv>\n";
    echo "    Example: php create_accounts.php users.csv\n";
    exit(1);
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    echo "[!] Error: File not found: {$csvFile}\n";
    exit(1);
}

if (!is_readable($csvFile)) {
    echo "[!] Error: File is not readable: {$csvFile}\n";
    exit(1);
}

// Open the CSV file
$handle = fopen($csvFile, 'r');
if ($handle === false) {
    echo "[!] Error: Could not open file: {$csvFile}\n";
    exit(1);
}

// Read and validate the header row
$header = fgetcsv($handle);
if ($header === false) {
    echo "[!] Error: CSV file is empty.\n";
    fclose($handle);
    exit(1);
}

// Normalize header names (trim whitespace, lowercase for matching)
$header = array_map('trim', $header);
$headerMap = array_flip(array_map('strtolower', $header));

$requiredColumns = ['username', 'email', 'name', 'password'];
foreach ($requiredColumns as $col) {
    if (!isset($headerMap[$col])) {
        echo "[!] Error: Missing required column '{$col}' in CSV header.\n";
        echo "    Found columns: " . implode(', ', $header) . "\n";
        fclose($handle);
        exit(1);
    }
}

$colUsername = $headerMap['username'];
$colEmail    = $headerMap['email'];
$colName     = $headerMap['name'];
$colPassword = $headerMap['password'];

echo "[i] Reading from: {$csvFile}\n";
echo "========================================\n";

try {
    $pdo = DB::pdo();

    // Prepare the secure insertion statement
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, balance) VALUES (?, ?, ?, 100)");

    $successCount = 0;
    $rowNumber = 1; // Start after the header

    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;

        // Skip completely empty rows
        if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
            continue;
        }

        // Validate the row has enough columns
        if (count($row) <= max($colUsername, $colEmail, $colName, $colPassword)) {
            echo "[!] Row {$rowNumber}: Skipped — not enough columns.\n";
            continue;
        }

        $username = trim($row[$colUsername]);
        $email    = trim($row[$colEmail]);
        $name     = trim($row[$colName]);
        $password = trim($row[$colPassword]);

        // Basic field validation
        if ($username === '' || $email === '' || $password === '') {
            echo "[!] Row {$rowNumber}: Skipped — username, email, or password is empty.\n";
            continue;
        }

        // Use the exact same hashing algorithm as the main application
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt->execute([$username, $email, $hash]);
            echo "[+] Created user: {$username} ({$name}) | Email: {$email} | Initial Balance: Rs. 100\n";
            $successCount++;
        } catch (\PDOException $e) {
            // Error code 23000 is for unique constraint violation (duplicate user)
            if ($e->getCode() == 23000) {
                echo "[-] Skipped user: {$username} (Already exists in database)\n";
            } else {
                echo "[!] Error creating {$username}: " . $e->getMessage() . "\n";
            }
        }
    }

    fclose($handle);

    echo "========================================\n";
    echo "✅ Successfully created {$successCount} accounts.\n";
    echo "========================================\n";

} catch (\Exception $e) {
    fclose($handle);
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}