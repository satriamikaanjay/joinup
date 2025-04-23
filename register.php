<?php
session_start();
include 'koneksi.php';

// Cek apakah form registrasi sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Periksa apakah username sudah ada di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $error_message = "Username sudah terdaftar!";
    } else {
        // Periksa apakah email sudah ada di database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $email_exists = $stmt->fetch();

        if ($email_exists) {
            $error_message = "Email sudah terdaftar!";
        } else {
            // Enkripsi password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Menyimpan data pengguna baru
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, tanggal_daftar) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$username, $hashed_password, $email]);

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $pdo->lastInsertId(); // Ambil ID pengguna yang baru terdaftar
            header("Location: profile.php"); // Arahkan ke halaman profil setelah pendaftaran
            exit;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Akun - JoinUp</title>
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-green-500 text-white flex items-center justify-center p-6">

 <!-- Loader screen -->
 <div id="loader" class="fixed inset-0 flex items-center justify-center bg-black/70 z-50">
  <img src="aset/bulat.png" alt="Loading..." class="w-20 h-20 animate-pulse">
</div>



  <div class="w-full max-w-md">
  
    <h1 class="text-3xl font-bold mb-8 text-center">Buat Akun</h1>

    <form action="register.php" method="POST" class="space-y-6">
      <div class="relative">
        <label class="absolute top-2 left-3 text-white"><i class="fas fa-user"></i></label>
        <input type="text" name="username" placeholder="username"
          class="w-full pl-10 py-2 bg-transparent border-b border-white placeholder-white focus:outline-none">
      </div>
      <div class="relative">
        <label class="absolute top-2 left-3 text-white"><i class="fas fa-lock"></i></label>
        <input type="password" name="password" id="password" placeholder="password"
          class="w-full pl-10 py-2 bg-transparent border-b border-white placeholder-white focus:outline-none">
        <button type="button" onclick="togglePassword()" class="absolute right-3 top-2 text-white">
          <i id="toggleIcon" class="far fa-eye"></i>
        </button>
      </div>
      <div class="relative">
        <label class="absolute top-2 left-3 text-white"><i class="fas fa-envelope"></i></label>
        <input type="email" name="email" placeholder="email"
          class="w-full pl-10 py-2 bg-transparent border-b border-white placeholder-white focus:outline-none">
      </div>
      <?php if (isset($error_message)): ?>
    <p class="text-center" style="color: red;"><?php echo $error_message; ?></p>
<?php endif; ?>
      <button type="submit"
        class="w-full bg-black text-white py-2 rounded-md hover:bg-opacity-80 transition">Daftar</button>
    </form>

    <p class="text-center mt-4 text-white text-sm">
      Sudah memiliki akun?? <a href="login.php" class="underline">Login disini</a>
    </p>
  </div>

  <script>
    function togglePassword() {
      const pass = document.getElementById("password");
      const icon = document.getElementById("toggleIcon");
      if (pass.type === "password") {
        pass.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        pass.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }

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


