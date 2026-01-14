<?php
// Simple test to check if routing works
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== ROUTING TEST ===\n\n";

// Simulate different URLs
$testUrls = [
    '/',
    '/login',
    '/admin/login',
    '/admin',
];

foreach ($testUrls as $url) {
    echo "Testing URL: $url\n";
    $_SERVER['REQUEST_URI'] = $url;
    $_SERVER['REQUEST_METHOD'] = 'GET';

    try {
        require_once __DIR__ . '/../config/config.php';
        require_once __DIR__ . '/../vendor/autoload.php';

        $router = new App\Core\Router();
        require_once __DIR__ . '/../app/routes.php';

        echo "  ✓ Routes loaded\n";

        // Don't actually dispatch, just check if route exists
        echo "\n";
    } catch (\Throwable $e) {
        echo "  ✗ ERROR: " . $e->getMessage() . "\n";
        echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    }
}

echo "=== TEST COMPLETE ===\n";
?>