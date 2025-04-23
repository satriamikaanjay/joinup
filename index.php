<?php
session_set_cookie_params([
  'lifetime' => 86400,
  'path' => '/',
  'domain' => $_SERVER['HTTP_HOST'],
  'secure' => isset($_SERVER['HTTPS']),
  'httponly' => true,
  'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', 86400);

session_start();
include 'koneksi.php';

// Cek session biasa dan rememberme
if (!isset($_SESSION['user_id']) && isset($_COOKIE['rememberme'])) {
    $token = $_COOKIE['rememberme'];
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id_user'];
    } else {
        setcookie('rememberme', '', time() - 3600, '/');
    }
}

// Ambil profil
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $profile_picture = !empty($user['profile_picture'])
        ? 'uploads/' . $user['profile_picture']
        : 'uploads/default.jpg';
} else {
    $profile_picture = 'uploads/default.jpg';
}

// Ambil data event
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Beranda Event</title>
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }

    /* Toast Notification Styles */
.toast-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

.toast {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  margin-bottom: 10px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  color: white;
  transform: translateX(150%);
  transition: transform 0.3s ease-in-out;
  max-width: 300px;
}

.toast.show {
  transform: translateX(0);
}

.toast.success {
  background-color: #2DCC70;
}

.toast.error {
  background-color: #E74C3C;
}

.toast-icon {
  margin-right: 12px;
  font-size: 20px;
}

.toast-close {
  margin-left: auto;
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.toast-close:hover {
  opacity: 1;
}

/* Animations */
@keyframes slideIn {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}

@keyframes fadeOut {
  from { opacity: 1; }
  to { opacity: 0; }
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
  <h1 class="text-3xl font-bold mb-8 text-center text-[#183727]">Daftar Event</h1>

  <?php if (count($events) > 0): ?>
    <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($events as $event): ?>
      <?php
        // Status event dengan tiga kondisi
        $today     = new DateTime('today');
        $startDate = new DateTime($event['event_date']);
        $endDate   = isset($event['end_date'])
                     ? new DateTime($event['end_date'])
                     : clone $startDate;
        
        // Kondisi sebelum start
        if ($startDate > $today) {
            $days_left    = $today->diff($startDate)->days;  // hari penuh
            $event_status = "Event akan dimulai dalam $days_left hari";
            $status_color = "bg-green-200 text-green-800";
        
        // Kondisi sedang berlangsung (hari ini antara start dan end inklusif)
        } elseif ($today >= $startDate && $today <= $endDate) {
            $days_remain  = $today->diff($endDate)->days;
            $event_status = "Event sedang berlangsung, sisa $days_remain hari";
            $status_color = "bg-yellow-200 text-yellow-800";
        
        // Kondisi setelah end
        } else {
            $event_status = "Event sudah selesai";
            $status_color = "bg-gray-200 text-gray-800";
        }

        // Cek mark
        $is_marked = false;
        if (isset($_SESSION['user_id'])) {
            $stmt2 = $pdo->prepare("SELECT 1 FROM marked_events WHERE user_id = ? AND event_id = ?");
            $stmt2->execute([$_SESSION['user_id'], $event['id_event']]);
            $is_marked = (bool) $stmt2->fetch();
        }
      ?>
       <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
    <div class="aspect-video overflow-hidden">
      <img src="<?= htmlspecialchars($event['event_poster']) ?>" alt="Poster Event"
           class="w-full h-full object-cover transform hover:scale-105 transition duration-300">
    </div>
    <div class="p-4">
      <!-- Status di atas judul -->
      <div class="mb-2">
        <span class="px-3 py-1 rounded-full text-sm <?= $status_color ?>">
          <?= $event_status ?>
        </span>
      </div>
      <!-- Judul event -->
      <h2 class="text-xl font-semibold text-gray-800 mb-4">
        <?= htmlspecialchars($event['event_name']) ?>
      </h2>

      <div class="flex items-center text-gray-500 mb-4">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span><?= htmlspecialchars($event['event_date']) ?></span>
      </div>

      <div class="flex gap-2">
        <button onclick="markEvent(<?= $event['id_event'] ?>, this)"
                class="flex-1 py-2 px-4 <?= $is_marked ? 'bg-green-600' : 'bg-[#2DCC70]' ?> hover:bg-[#25b562] text-white rounded-lg transition-colors">
          <?= $is_marked ? '✓ Disimpan' : 'Simpan' ?>
        </button>
        <button onclick="window.location.href='event_detail.php?id=<?= $event['id_event'] ?>'"
                class="flex-1 py-2 px-4 border-2 border-[#2DCC70] text-[#183727] hover:bg-[#2DCC70]/10 rounded-lg transition-colors">
          Detail
        </button>
      </div>
    </div>
  </div>
<?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-12">
      <p class="text-gray-500 mb-4">😔</p>
      <p class="text-gray-600">Belum ada event yang tersedia</p>
    </div>
  <?php endif; ?>
</main>

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


<!-- Modal -->
<div id="eventModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 backdrop-blur-sm">
  <div class="bg-white rounded-2xl max-w-md w-full mx-4 overflow-hidden">
    <div class="relative">
      <button onclick="closeModal()" class="absolute top-4 right-4 text-white bg-black/30 rounded-full p-1 hover:bg-black/40">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
      <img id="modalPoster" src="" alt="Poster" class="w-full h-48 object-cover">
    </div>
    
    <div class="p-6">
      <h2 id="modalTitle" class="text-2xl font-bold text-[#183727] mb-2"></h2>
      
      <div class="space-y-3 text-gray-600">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <p id="modalDate"></p>
        </div>
        
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <p id="modalLocation"></p>
        </div>
        
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p id="modalFee"></p>
        </div>
      </div>

      <p id="modalDescription" class="mt-4 text-gray-600 leading-relaxed"></p>
      
      <button id="ikutSekarangBtn" 
              class="w-full mt-6 py-3 bg-[#2DCC70] hover:bg-[#25b562] text-white rounded-lg font-medium transition-colors">
        Ikut Sekarang
      </button>
    </div>
  </div>
</div>

<div id="toast-container" class="toast-container"></div>

<!-- Script tetap sama -->
<script>
function markEvent(eventId, buttonElement) {
    fetch('mark_event_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'event_id=' + eventId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update tombol
            if (data.action === 'add') {
                buttonElement.classList.remove('bg-[#2DCC70]');
                buttonElement.classList.add('bg-green-600');
                buttonElement.textContent = '✓ Ditandai';
                showToast('Event berhasil disimpan, silahkan cek di profil', 'success');
            } else if (data.action === 'remove') {
                buttonElement.classList.remove('bg-green-600');
                buttonElement.classList.add('bg-[#2DCC70]');
                buttonElement.textContent = 'Simpan';
                showToast('Event berhasil dihapus', 'success');
            }
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error', 5000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Koneksi bermasalah', 'error', 5000);
    });
}

// Fungsi untuk menampilkan notifikasi toast (opsional)
function showToast(message, type = 'success', duration = 3000) {
  const toastContainer = document.getElementById('toast-container');
  
  // Buat elemen toast
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  // Icon berdasarkan type
  const icon = type === 'success' ? '✓' : '✕';
  
  // Isi toast
  toast.innerHTML = `
    <span class="toast-icon">${icon}</span>
    <span class="toast-message">${message}</span>
    <span class="toast-close">&times;</span>
  `;
  
  // Tambahkan ke container
  toastContainer.appendChild(toast);
  
  // Trigger animasi masuk
  setTimeout(() => toast.classList.add('show'), 10);
  
  // Fungsi close
  const closeToast = () => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  };
  
  // Close otomatis setelah duration
  const timeoutId = setTimeout(closeToast, duration);
  
  // Close manual ketika diklik
  toast.querySelector('.toast-close').addEventListener('click', () => {
    clearTimeout(timeoutId);
    closeToast();
  });
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
