<?php
require_once '../config/database.php';
include_once '../config/config.php';

session_destroy();

// Gunakan interpolasi variabel PHP secara benar
header("Location: {$base_url}auth/login.php");
exit();
