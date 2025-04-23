<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['user'])) {
    header('Location: login.php');
    exit;
}

$username = $_GET['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Cari user dengan username dan email yang cocok
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate token reset
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id_user = ?");
        $stmt->execute([$token, $user['id_user']]);

        // Simulasi "kirim email" ➔ karena localhost, tampilkan link reset
        $_SESSION['reset_link'] = "http://localhost/reset_password.php?token=$token";
        header('Location: reset_link.php');
        exit;
    } else {
        $error = "Email tidak cocok dengan user $username!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - <?= htmlspecialchars($username) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-green-500">
<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold mb-4 text-center">Lupa Password</h1>
    <p class="text-center mb-6">Masukkan email akun <strong><?= htmlspecialchars($username) ?></strong></p>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <input type="email" name="email" required placeholder="Masukkan email..." class="w-full border px-4 py-2 rounded">
        <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white py-2 rounded">Kirim Link Reset</button>
    </form>
</div>
</body>
</html>