<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== DEBUG TEST v2 ===\n\n";

echo "1. Testing config.php loading...\n";
require_once __DIR__ . '/../config/config.php';
echo "   ✓ Config loaded successfully\n\n";

echo "2. BASE_URL: " . BASE_URL . "\n\n";

echo "3. Server Variables:\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "   HTTPS: " . ($_SERVER['HTTPS'] ?? 'NOT SET') . "\n";
echo "   SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'NOT SET') . "\n";
echo "   REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'NOT SET') . "\n";
echo "   HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'NOT SET') . "\n";
echo "   HTTP_X_FORWARDED_SSL: " . ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? 'NOT SET') . "\n\n";

echo "4. Testing autoloader...\n";
require_once __DIR__ . '/../vendor/autoload.php';
echo "   ✓ Autoloader loaded successfully\n\n";

echo "=== ALL TESTS PASSED ===\n";
?>