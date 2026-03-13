<?php
declare(strict_types=1);

// Run this script from the terminal: php create_accounts.php
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

require_once __DIR__ . '/app/Core/config.php';
require_once __DIR__ . '/app/Core/db.php';

use App\Core\DB;

echo "========================================\n";
echo "🛡️ SecureApp Auto-Account Generator 🛡️\n";
echo "========================================\n";

$users = [
    ['username' => 'alice',   'email' => 'alice@secureapp.local',   'password' => 'G7!kL2@pQ9x#'],
    ['username' => 'bob',     'email' => 'bob@secureapp.local',     'password' => 'T4$zA8!mR2&y'],
    ['username' => 'charlie', 'email' => 'charlie@secureapp.local', 'password' => 'Q!6bV3@wL9$e'],
    ['username' => 'dave',    'email' => 'dave@secureapp.local',    'password' => 'H2@X7!qM5$kP'],
    ['username' => 'eve',   'email' => 'eve@secureapp.local',   'password' => 'Z!8dR4@tY6#s'],
];

try {
    $pdo = DB::pdo();
    
    // Prepare the secure insertion statement
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, balance) VALUES (?, ?, ?, 100)");

    $successCount = 0;

    foreach ($users as $user) {
        // Use the exact same hashing algorithm as the main application
        $hash = password_hash($user['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt->execute([$user['username'], $user['email'], $hash]);
            echo "[+] Created user: {$user['username']} | Pass: {$user['password']} | Initial Balance: Rs. 100\n";
            $successCount++;
        } catch (\PDOException $e) {
            // Error code 23000 is for unique constraint violation (duplicate user)
            if ($e->getCode() == 23000) {
                echo "[-] Skipped user: {$user['username']} (Already exists in database)\n";
            } else {
                echo "[!] Error creating {$user['username']}: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "========================================\n";
    echo "✅ Successfully created {$successCount} accounts.\n";
    echo "========================================\n";

} catch (\Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}