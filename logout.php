<?php
session_start();

// Hapus semua data session
$_SESSION = [];
session_unset();
session_destroy();

// Hapus cookie 'rememberme' juga
if (isset($_COOKIE['rememberme'])) {
    setcookie('rememberme', '', time() - 3600, '/');
}

// Redirect ke halaman login (atau index.php)
header("Location: login.php");
exit;
?>
