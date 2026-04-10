<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "PHP is working\n";

// Test 1: Can we find autoload?
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die("ERROR: vendor/autoload.php not found. Path: " . __DIR__);
}
echo "Autoload found\n";

// Test 2: Can we load it?
require __DIR__ . '/../vendor/autoload.php';
echo "Autoload loaded\n";

// Test 3: Can we boot Laravel?
$app = require_once __DIR__ . '/../bootstrap/app.php';
echo "Laravel booted\n";