<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id_event = $_GET['id'];

    // Ambil data event berdasarkan id_event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id_event = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch();

    // Jika event ditemukan, kirim data sebagai JSON
    if ($event) {
        echo json_encode($event);
    } else {
        echo json_encode(["error" => "Event tidak ditemukan"]);
    }
} else {
    echo json_encode(["error" => "ID event tidak diberikan"]);
}
?>
