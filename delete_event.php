<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Cek apakah ada parameter 'id' yang dikirimkan
if (isset($_GET['id'])) {
    $id_event = $_GET['id'];

    // Hapus event dari database
    $stmt = $pdo->prepare("DELETE FROM events WHERE id_event = ?");
    $stmt->execute([$id_event]);

    // Redirect kembali ke halaman admin setelah penghapusan
    header("Location: admin.php");
    exit;
} else {
    // Jika tidak ada id yang dikirimkan, redirect ke halaman admin
    header("Location: admin.php");
    exit;
}
?>
