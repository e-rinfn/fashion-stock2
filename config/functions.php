<?php
require_once 'database.php';

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn()
{
    if (!isLoggedIn()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function checkRole($requiredRole)
{
    if ($_SESSION['role'] != $requiredRole) {
        header("Location: ../index.php");
        exit();
    }
}

function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// config/functions.php
function loadEnv($path = __DIR__ . '/../.env')
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split into key and value
        list($name, $value) = array_map('trim', explode('=', $line, 2));

        // Remove quotes if present
        $value = trim($value, "\"'");

        // Set environment variable
        $_ENV[$name] = $value;
    }
}

loadEnv();
