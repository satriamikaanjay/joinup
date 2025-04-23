<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mengambil data admin berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    // Verifikasi password admin
    if ($admin && $password == $admin['password']) {
        // Jika username dan password cocok
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];  // Menyimpan ID admin di session
        header("Location: admin.php");
        exit;
    } else {
        // Jika login gagal
        $error_message = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Login Admin</h1>
        <?php if (isset($error_message)) { echo "<p class='text-red-500 text-center mb-4'>$error_message</p>"; } ?>
        <form action="login_admin.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username:</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password:</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Login
            </button>
        </form>
    </div>
</body>
</html>