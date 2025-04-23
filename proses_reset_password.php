<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    // Validasi basic
    if (empty($token) || empty($new_password)) {
        die("Data tidak lengkap!");
    }

    // Cek token valid
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Token tidak valid atau sudah kadaluarsa!");
    }

    // Hash password baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password baru dan hapus reset_token
    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
    $stmt->execute([$hashed_password, $user['id']]);

    echo "Password berhasil direset! Silahkan login kembali.";
} else {
    die("Metode tidak diperbolehkan.");
}
?>
