<?php
session_start();
include 'koneksi.php';

// Pastikan admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_event = $_POST['id_event'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_description = $_POST['event_description'];
    $event_location = $_POST['event_location']; // Tambahan lokasi
    $event_fee = $_POST['event_fee']; // Tambahan biaya
    $event_link = $_POST['event_link']; // Ambil link event

    // Ambil event lama
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id_event = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch();
    if (!$event) {
        echo "Event tidak ditemukan.";
        exit;
    }

    $poster_path = $event['event_poster']; // default pakai poster lama

    // Cek upload poster baru
    if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] == 0) {
        $file_tmp = $_FILES['event_poster']['tmp_name'];
        $file_name = $_FILES['event_poster']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array(strtolower($file_ext), $allowed_ext)) {
            $new_file_name = 'poster_' . time() . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Hapus poster lama
                if (file_exists($poster_path)) {
                    unlink($poster_path);
                }
                $poster_path = $upload_path;
            } else {
                echo "Gagal upload poster baru.";
                exit;
            }
        } else {
            echo "Format gambar tidak valid.";
            exit;
        }
    }

    // Update data ke database
    $stmt = $pdo->prepare("UPDATE events SET event_name = ?, event_date = ?, event_description = ?, event_poster = ?, event_location = ?, event_fee = ?, event_link = ? WHERE id_event = ?");
    $stmt->execute([
        $event_name,
        $event_date,
        $event_description,
        $poster_path,
        $event_location,
        $event_fee,
        $event_link, // Menyimpan link event
        $id_event
    ]);

    header("Location: admin.php");
    exit;
}
?>
