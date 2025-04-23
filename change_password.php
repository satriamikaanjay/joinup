<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Ambil data pengguna dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi form
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua field harus diisi.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Password lama salah.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi tidak cocok.";
    } else {
        // Hash password baru
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update ke database
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        $update->execute([$new_password_hashed, $_SESSION['user_id']]);

        $success = "Password berhasil diganti.";
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile picture
$profile_picture_name = $user['profile_picture'] ?? 'default.jpg';
$profile_picture = 'uploads/' . htmlspecialchars($profile_picture_name);

// Handle username and email
$username = htmlspecialchars($user['username'] ?? 'User');
$email = htmlspecialchars($user['email'] ?? 'Not provided');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ganti Password</title>
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<?php
include 'navbar.php';
?>

  <main class="flex-grow container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="bg-[#183727] p-6 text-center">
        <h1 class="text-2xl font-bold text-white">Ganti Password</h1>
      </div>
      <div class="p-6 space-y-4">
        <?php if (!empty($error)): ?>
          <div class="bg-red-100 text-red-700 px-4 py-3 rounded">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="bg-green-100 text-green-700 px-4 py-3 rounded">
            <?= htmlspecialchars($success) ?>
          </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
          <div>
            <label for="current_password" class="block text-gray-700 font-medium mb-1">Password Lama</label>
            <input
              id="current_password"
              name="current_password"
              type="password"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2DCC70]"
              placeholder="Masukkan password lama"
            />
          </div>

          <div>
            <label for="new_password" class="block text-gray-700 font-medium mb-1">Password Baru</label>
            <input
              id="new_password"
              name="new_password"
              type="password"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2DCC70]"
              placeholder="Masukkan password baru"
            />
          </div>

          <div>
            <label for="confirm_password" class="block text-gray-700 font-medium mb-1">Konfirmasi Password Baru</label>
            <input
              id="confirm_password"
              name="confirm_password"
              type="password"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2DCC70]"
              placeholder="Konfirmasi password baru"
            />
          </div>

          <button
            type="submit"
            class="w-full bg-[#2DCC70] hover:bg-[#25b562] text-white py-2 rounded-lg font-semibold transition-colors"
          >
            Simpan Password Baru
          </button>
        </form>

        <a
          href="profile.php"
          class="block text-center text-sm text-gray-600 hover:text-[#183727] mt-4"
        >
          ← Kembali ke Profil
        </a>
      </div>
    </div>
  </main>



</body>
</html>
