<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_logged_in'])) {
    // Jika belum login, arahkan ke halaman login dengan pesan
    $_SESSION['message'] = "Harap login untuk menandai event!";
    header("Location: login.php");
    exit;
}

// Jika sudah login, simpan tanda event di database
if (isset($_SESSION['user_logged_in'])) {
    $user_id = $_SESSION['user_id'];  // Misal user_id ada di session
    $event_id = $_GET['event_id'];

    // Insert ke tabel mark_event (atau tabel lain sesuai desain database)
    $stmt = $pdo->prepare("INSERT INTO marked_events (user_id, event_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $event_id]);

    // Setelah berhasil menandai event, arahkan ke halaman profile
    header("Location: profile.php");
    exit;
}
?>
