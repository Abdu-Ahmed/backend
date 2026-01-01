<?php
// test-db.php
header('Content-Type: text/plain');
echo "Testing Database Connection...\n\n";

try {
    // Load config
    $config = [
        'host' => getenv('DB_HOST') ?: 'caboose.proxy.rlwy.net',
        'port' => getenv('DB_PORT') ?: '3306',
        'dbname' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'password' => getenv('DB_PASSWORD'),
    ];
    
    echo "Config:\n";
    print_r($config);
    
    echo "\nConnecting...\n";
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3
    ]);
    
    echo "✓ Connected successfully!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "Query test: " . ($result['test'] == 1 ? 'PASS' : 'FAIL') . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}