<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Proses untuk menandai event
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $user_id = $_SESSION['user_id']; // Pastikan session user_id sudah diatur saat login

    // Cek apakah event sudah pernah ditandai
    $stmt = $pdo->prepare("SELECT * FROM marked_events WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$user_id, $event_id]);
    $existing_event = $stmt->fetch();

    if (!$existing_event) {
        // Masukkan event yang ditandai ke dalam database
        $stmt = $pdo->prepare("INSERT INTO marked_events (user_id, event_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $event_id]);

        // Tampilkan notifikasi sukses
        echo "Event berhasil ditandai!";
    } else {
        echo "Anda sudah menandai event ini sebelumnya.";
    }
} else {
    echo "Event tidak ditemukan.";
}
?>
