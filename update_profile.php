<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid('profile_') . '.' . $extension;
    $uploadPath = 'uploads/' . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id_user = ?");
        $stmt->execute([$newFilename, $_SESSION['user_id']]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?>
