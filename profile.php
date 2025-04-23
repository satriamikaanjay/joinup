<?php
session_start();
include 'koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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

$current = basename($_SERVER['PHP_SELF']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil Pengguna</title>
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
    
    /* Preview Modal Styles */
    .preview-modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 50;
    }
    .preview-container {
      background: #fff;
      padding: 1.5rem;
      border-radius: 0.75rem;
      max-width: 90%;
      width: 350px;
      text-align: center;
    }
    #imagePreview {
      max-width: 100%;
      max-height: 300px;
      margin: 0 auto 1rem;
      border-radius: 0.5rem;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">


<!-- Header -->
<?php
include 'navbar.php';
?>

<!-- Main Content -->
<main class="flex-grow container mx-auto px-4 py-8">
  <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- Profile Header -->
    <div class="bg-[#183727] p-6 text-center">
      <div class="relative inline-block">
        <img src="<?= $profile_picture ?>" 
             alt="Foto Profil" 
             class="w-32 h-32 rounded-full border-4 border-white object-cover mx-auto">
        <label for="profile_picture" class="absolute bottom-0 right-0 bg-[#2DCC70] text-white p-2 rounded-full cursor-pointer hover:bg-[#25b562]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
          </svg>
          <input type="file" id="profile_picture" name="profile_picture" class="hidden" accept="image/*" onchange="previewImage(event)">
        </label>
      </div>
      <h1 class="text-2xl font-bold text-white mt-4"><?= $username ?></h1>
    </div>
    
    <!-- Profile Details -->
    <div class="p-6 space-y-6">
      <!-- User Information -->
      <div class="space-y-4">
        <h2 class="text-xl font-semibold text-[#183727] border-b pb-2">Informasi Akun</h2>
        
        <div class="flex items-center">
          <svg class="w-6 h-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <div>
            <p class="text-gray-600">Username</p>
            <p class="font-medium"><?= $username ?></p>
          </div>
        </div>
        
        <div class="flex items-center">
          <svg class="w-6 h-6 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          <div>
            <p class="text-gray-600">Email</p>
            <p class="font-medium"><?= $email ?></p>
          </div>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="space-y-4 pt-4">
        <h2 class="text-xl font-semibold text-[#183727] border-b pb-2">Pengaturan Akun</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a href="change_password.php" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div class="p-3 bg-blue-100 rounded-full mr-4">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-medium">Ganti Password</h3>
              <p class="text-sm text-gray-500">Perbarui kata sandi Anda</p>
            </div>
          </a>
          
          <a href="mark_event.php" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div class="p-3 bg-green-100 rounded-full mr-4">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-medium">Event Tersimpan</h3>
              <p class="text-sm text-gray-500">Lihat event yang Anda tandai</p>
            </div>
          </a>
        </div>
      </div>
      
      <!-- Logout Button -->
      <div class="pt-4">
        <a href="logout.php" class="w-full block text-center py-3 px-6 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
          Keluar
        </a>
      </div>
    </div>
  </div>
</main>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal">
  <div class="preview-container">
    <img id="imagePreview">
    <p class="mb-4">Apakah Anda ingin menggunakan foto ini?</p>
    <div class="flex justify-center gap-4">
      <button onclick="uploadImage()" class="px-4 py-2 bg-[#2DCC70] hover:bg-[#25b562] text-white rounded-lg">
        Ya, Gunakan
      </button>
      <button onclick="closePreview()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
        Batal
      </button>
    </div>
  </div>
</div>

<!-- Bottom Navigation -->
<nav class="sticky bottom-0 bg-white border-t border-gray-200 shadow-lg">
  <div class="container mx-auto">
    <div class="flex justify-around py-2">
      <!-- Beranda -->
      <a href="index.php"
         class="group relative flex flex-col items-center px-4 py-2 transition-all duration-300
                <?= $current === 'index.php' ? '-translate-y-2 shadow-xl' : '' ?>">
        <div class="relative">
          <i class="ri-home-4-line text-2xl <?= $current === 'index.php' ? 'text-[#2DCC70]' : 'text-gray-500 group-hover:text-[#2DCC70]' ?>
                         transition-colors duration-300"></i>
        </div>
        <span class="text-xs mt-1 <?= $current === 'index.php' ? 'text-[#2DCC70]' : 'text-gray-600 group-hover:text-[#2DCC70]' ?>
                           transition-colors duration-300">
          Beranda
        </span>
      </a>

      <!-- Cari -->
      <a href="search.php"
         class="group relative flex flex-col items-center px-4 py-2 transition-all duration-300
                <?= $current === 'search.php' ? '-translate-y-2 shadow-xl' : '' ?>">
        <div class="relative">
          <i class="ri-search-line text-2xl <?= $current === 'search.php' ? 'text-[#2DCC70]' : 'text-gray-500 group-hover:text-[#2DCC70]' ?>
                         transition-colors duration-300"></i>
        </div>
        <span class="text-xs mt-1 <?= $current === 'search.php' ? 'text-[#2DCC70]' : 'text-gray-600 group-hover:text-[#2DCC70]' ?>
                           transition-colors duration-300">
          Cari
        </span>
      </a>

      <!-- Profil -->
      <a href="profile.php"
         class="group relative flex flex-col items-center px-4 py-2 transition-all duration-300
                <?= $current === 'profile.php' ? '-translate-y-2 shadow-xl' : '' ?>">
        <div class="relative">
          <i class="ri-user-3-line text-2xl <?= $current === 'profile.php' ? 'text-[#2DCC70]' : 'text-gray-500 group-hover:text-[#2DCC70]' ?>
                         transition-colors duration-300"></i>
        </div>
        <span class="text-xs mt-1 <?= $current === 'profile.php' ? 'text-[#2DCC70]' : 'text-gray-600 group-hover:text-[#2DCC70]' ?>
                           transition-colors duration-300">
          Profil
        </span>
      </a>
    </div>
  </div>
</nav>

<script>
let selectedFile;

function previewImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  
  // Validate file type
  if (!file.type.match('image.*')) {
    alert('Silakan pilih file gambar (JPEG, PNG)');
    return;
  }
  
  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('Ukuran file maksimal 5MB');
    return;
  }
  
  selectedFile = file;
  const reader = new FileReader();
  
  reader.onload = function(e) {
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.src = e.target.result;
    document.getElementById('previewModal').style.display = 'flex';
  };
  
  reader.readAsDataURL(file);
}

function uploadImage() {
  if (!selectedFile) return;
  
  const formData = new FormData();
  formData.append('profile_picture', selectedFile);

  fetch('update_profile.php', {
    method: 'POST',
    body: formData,
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closePreview();
      showToast('Foto profil berhasil diperbarui');
      setTimeout(() => location.reload(), 1500); // Reload setelah 1.5 detik
    } else {
      alert('Gagal upload foto: ' + data.message);
    }
  })
  .catch(error => {
    console.error(error);
    alert('Terjadi kesalahan saat mengupload foto');
  });
}

function closePreview() {
  document.getElementById('previewModal').style.display = 'none';
  document.getElementById('profile_picture').value = '';
}

function showToast(message) {
  const toast = document.createElement('div');
  toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg';
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.remove();
  }, 3000);
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