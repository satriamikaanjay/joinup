<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Cek apakah event sudah ditandai sebelumnya
        $stmt = $pdo->prepare("SELECT * FROM marked_events WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Jika sudah ditandai, hapus dari marked
            $stmt = $pdo->prepare("DELETE FROM marked_events WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$user_id, $event_id]);
            echo json_encode(['status' => 'success', 'message' => 'Event berhasil dihapus dari marked', 'action' => 'remove']);
        } else {
            // Jika belum, tambahkan ke marked
            $stmt = $pdo->prepare("INSERT INTO marked_events (user_id, event_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $event_id]);
            echo json_encode(['status' => 'success', 'message' => 'Event berhasil ditandai', 'action' => 'add']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}