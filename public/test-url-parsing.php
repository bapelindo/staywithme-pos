<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== URL PARSING TEST ===\n\n";

// Simulate production environment
$_SERVER['HTTP_HOST'] = 'bacelor.bapel.my.id';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['REQUEST_URI'] = '/login';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../config/config.php';

echo "BASE_URL: " . BASE_URL . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n\n";

// Parse BASE_URL
$basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
echo "Base Path from BASE_URL: '" . $basePath . "'\n";

// Parse REQUEST_URI
$fullPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
echo "Full Path from REQUEST_URI: '" . $fullPath . "'\n";

// Calculate relative path
$relativePath = '';
if (!empty($basePath)) {
    if (str_starts_with($fullPath, $basePath . '/')) {
        $relativePath = substr($fullPath, strlen($basePath) + 1);
    } elseif ($fullPath === $basePath) {
        $relativePath = '';
    } else {
        $relativePath = $fullPath;
    }
} else {
    $relativePath = $fullPath;
}

echo "Relative Path (what Router will match): '" . $relativePath . "'\n\n";

echo "=== CONCLUSION ===\n";
echo "Router will try to match: '" . $relativePath . "'\n";
echo "Available routes include: '/', 'admin/login', etc.\n";
echo "Route '/login' does NOT exist, so it will return 404.\n";
?>