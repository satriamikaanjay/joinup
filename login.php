<?php
session_start();
include 'koneksi.php';

// Cek apakah form login sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']); // Ini ambil remember me

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session biasa
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id_user'];

            if ($remember) {
                // Kalau remember me dicentang
                $token = bin2hex(random_bytes(32)); // generate token random
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id_user = ?");
                $stmt->execute([$token, $user['id_user']]);

                // Simpan token di cookie, expire 10 tahun
                setcookie('rememberme', $token, time() + (10 * 365 * 24 * 60 * 60), '/', $_SERVER['HTTP_HOST'], isset($_SERVER['HTTPS']), true);
            }

            // Redirect
            if (isset($_SESSION['redirect_to'])) {
                $redirect_url = $_SESSION['redirect_to'];
                unset($_SESSION['redirect_to']);
                header("Location: $redirect_url");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error_message = "Username atau password salah!";
        }
    } else {
        $error_message = "Username atau password tidak boleh kosong!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JoinUp</title>
    <link rel="icon" href="aset/bulat.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script> 
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="h-screen flex flex-col md:flex-row">

<div id="loader" class="fixed inset-0 flex items-center justify-center bg-black/70 z-50">
  <img src="aset/bulat.png" alt="Loading..." class="w-20 h-20 animate-pulse">
</div>

    <!-- Bagian Kiri -->
    <div class="w-full md:w-2/3 bg-white flex flex-col items-center justify-center p-10 relative">
        <!-- Gambar menggunakan background -->
        <div class="w-full h-full absolute top-0 left-0 bg-cover bg-center bg-no-repeat" style="background-image: url('aset/login.jpg');">
            <!-- Background hexagon -->
            <div class="absolute inset-0 opacity-10 bg-[url('hexagon-background.png')] bg-center bg-no-repeat bg-cover"></div>
        </div>

        <!-- Logo di pojok kanan atas -->
        <div class="absolute top-6 right-6 flex items-center gap-2 z-10">
            <img src="aset/bulat.png" alt="Logo JoinUp" class="w-20">
        </div>
    </div>

    <!-- Bagian Kanan -->
    <div class="w-full md:w-1/3 bg-green-500 flex flex-col justify-center items-center relative p-10 min-h-screen">
    <h1 class="text-3xl font-bold text-white mb-2">Selamat Datang !!!</h1>
    <p class="text-white mb-8">Silahkan login terlebih dahulu</p>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center w-full max-w-sm">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="w-full max-w-sm space-y-6">
        <div class="relative">
            <i class="ri-user-line absolute top-3 left-3 text-white"></i>
            <input type="text" name="username" required placeholder="username" class="w-full pl-10 pr-4 py-2 rounded bg-transparent border-b border-white text-white placeholder-white focus:outline-none">
        </div>

        <div class="relative">
            <i class="ri-lock-line absolute top-3 left-3 text-white"></i>
            <input type="password" name="password" id="password" required placeholder="password" class="w-full pl-10 pr-10 py-2 rounded bg-transparent border-b border-white text-white placeholder-white focus:outline-none">
            <i id="togglePassword" class="ph ph-eye-slash absolute top-3 right-3 text-gray-500 cursor-pointer"></i>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center text-white text-sm">
            <input type="checkbox" name="remember" id="remember" class="mr-2">
            <label for="remember">Remember Me</label>
        </div>

        <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white py-2 rounded font-semibold">Login</button>
    </form>


    <p class="text-white text-sm mt-6">
        Tidak memiliki akun?? 
        <a href="register.php" class="underline font-semibold">Daftar disini</a>
    </p>
</div>


    <!-- Script untuk toggle password -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('ph-eye');
            togglePassword.classList.toggle('ph-eye-slash');
        });

        window.addEventListener("load", function () {
  const loader = document.getElementById("loader");

  let downlinkSpeed = navigator.connection ? navigator.connection.downlink : 10; // Default 10 Mbps kalau tidak tersedia

  let loadingTime;

  if (downlinkSpeed <= 0.5) { 
    // Kalau kecepatan kurang dari 0.5 Mbps (sangat lambat)
    loadingTime = 5000;
  } else if (downlinkSpeed <= 2) { 
    // Kalau antara 0.5 - 2 Mbps (lambat)
    loadingTime = 3000;
  } else if (downlinkSpeed <= 5) {
    // Kalau antara 2 - 5 Mbps (sedang)
    loadingTime = 2000;
  } else {
    // Kalau di atas 5 Mbps (cepat)
    loadingTime = 1000;
  }

  setTimeout(() => {
    loader.style.opacity = '0';
    loader.style.visibility = 'hidden';
    document.getElementById("content").classList.remove("hidden");
  }, loadingTime);
});

    </script>
</body>
</html>



