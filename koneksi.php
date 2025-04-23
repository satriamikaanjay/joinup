<?php
$host = 'centerbeam.proxy.rlwy.net';
$dbname = 'railway';
$username = 'root';
$password = 'nvvVEpgHcgoxoioIMwTmpYmPyqClfRYu'; // ganti dengan password yang kamu copy dari dashboard Railway
$port = 20107; // Ganti dengan password database Anda

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set mode error PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
