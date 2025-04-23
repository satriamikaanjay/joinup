<?php
session_start();
include 'koneksi.php';

// Ambil data profil user (sama seperti beranda.php)
if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id_user = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  if ($user && !empty($user['profile_picture'])) {
      $profile_picture = 'uploads/' . $user['profile_picture'];
  } else {
      $profile_picture = 'uploads/default.jpg';
  }
} else {
  $profile_picture = 'uploads/default.jpg';
}

// Ambil filter dari URL
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$jenis_event = isset($_GET['jenis_event']) ? $_GET['jenis_event'] : [];
$mode = isset($_GET['mode']) ? trim($_GET['mode']) : '';
$harga = isset($_GET['harga']) ? trim($_GET['harga']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

$events = [];

if (!empty($_GET)) {
    // Query filter (sama seperti sebelumnya)
    $query = "SELECT * FROM events WHERE 1=1";
    $params = [];

    if (!empty($keyword)) {
        $query .= " AND LOWER(event_name) LIKE ?";
        $params[] = '%' . strtolower($keyword) . '%';
    }

    if (!empty($jenis_event)) {
        $placeholders = implode(',', array_fill(0, count($jenis_event), '?'));
        $query .= " AND event_type IN ($placeholders)";
        foreach ($jenis_event as $je) {
            $params[] = $je;
        }
    }

    if (!empty($mode)) {
        $query .= " AND event_location = ?";
        $params[] = $mode;
    }

    if (!empty($harga)) {
        $query .= " AND event_fee = ?";
        $params[] = $harga;
    }

    if (!empty($start_date) && !empty($end_date)) {
        $query .= " AND event_date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$current = basename($_SERVER['PHP_SELF']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cari Event</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
    
    /* Custom checkbox style */
    .filter-checkbox:checked {
      background-color: #2DCC70;
      border-color: #2DCC70;
    }
    
    /* Toast Notification */
    .toast {
      animation: slideIn 0.3s forwards, fadeOut 0.5s forwards 2.5s;
    }
    
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes fadeOut {
      from { opacity: 1; }
      to { opacity: 0; }
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">



<!-- Header (Sama seperti beranda.php) -->
<?php
include 'navbar.php';
?>

<!-- Main Content -->
<main class="flex-grow container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-8 text-center text-[#183727]">Cari Event</h1>
  
  <!-- Filter Section -->
  <div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <button onclick="toggleFilter()" class="w-full md:w-auto px-4 py-2 bg-[#2DCC70] text-white rounded-lg mb-4 md:hidden">
      Tampilkan Filter
    </button>
    
    <form id="filterForm" action="search.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Keyword Search -->
      <div>
        <label class="block text-gray-700 mb-2">Cari Nama Event</label>
        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2DCC70]"
               placeholder="Masukkan nama event...">
      </div>
      
      <!-- Event Type -->
      <div>
        <label class="block text-gray-700 mb-2">Jenis Event</label>
        <div class="space-y-2">
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="jenis_event[]" value="Olimpiade" 
                   <?= in_array('Olimpiade', $jenis_event) ? 'checked' : '' ?>
                   class="filter-checkbox rounded border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Olimpiade</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="jenis_event[]" value="Bootcamp" 
                   <?= in_array('Bootcamp', $jenis_event) ? 'checked' : '' ?>
                   class="filter-checkbox rounded border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Bootcamp</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="checkbox" name="jenis_event[]" value="Webinar" 
                   <?= in_array('Webinar', $jenis_event) ? 'checked' : '' ?>
                   class="filter-checkbox rounded border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Webinar</span>
          </label>
        </div>
      </div>
      
      <!-- Mode -->
      <div>
        <label class="block text-gray-700 mb-2">Mode Event</label>
        <div class="space-y-2">
          <label class="flex items-center space-x-2">
            <input type="radio" name="mode" value="Offline" 
                   <?= $mode == 'Offline' ? 'checked' : '' ?>
                   class="rounded-full border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Offline</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="radio" name="mode" value="Online" 
                   <?= $mode == 'Online' ? 'checked' : '' ?>
                   class="rounded-full border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Online</span>
          </label>
        </div>
      </div>
      
      <!-- Price -->
      <div>
        <label class="block text-gray-700 mb-2">Harga</label>
        <div class="space-y-2">
          <label class="flex items-center space-x-2">
            <input type="radio" name="harga" value="Gratis" 
                   <?= $harga == 'Gratis' ? 'checked' : '' ?>
                   class="rounded-full border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Gratis</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="radio" name="harga" value="Bayar" 
                   <?= $harga == 'Bayar' ? 'checked' : '' ?>
                   class="rounded-full border-gray-300 text-[#2DCC70] focus:ring-[#2DCC70]">
            <span>Berbayar</span>
          </label>
        </div>
      </div>
      
      <!-- Date Range -->
      <div class="md:col-span-2">
        <label class="block text-gray-700 mb-2">Rentang Tanggal</label>
        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
          <div class="flex-1">
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2DCC70]">
          </div>
          <div class="flex-1">
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2DCC70]">
          </div>
        </div>
      </div>
      
      <!-- Submit Button -->
      <div class="md:col-span-3 flex justify-end">
        <button type="submit" class="px-6 py-2 bg-[#2DCC70] hover:bg-[#25b562] text-white rounded-lg transition-colors">
          Terapkan Filter
        </button>
      </div>
    </form>
  </div>

  <!-- Event Results -->
  <?php if (count($events) > 0): ?>
    <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($events as $event): ?>
        <?php
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
        ?>
        
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
          <div class="aspect-video overflow-hidden">
            <img src="<?= htmlspecialchars($event['event_poster']) ?>" alt="Poster Event" 
                 class="w-full h-full object-cover transform hover:scale-105 transition duration-300">
          </div>
          
          <div class="p-4">
          <div class="mb-2">
        <span class="px-3 py-1 rounded-full text-sm <?= $status_color ?>">
          <?= $event_status ?>
        </span>
      </div>
            <div class="flex justify-between items-start mb-2">
              <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($event['event_name']) ?></h2>
            </div>
            
            <div class="flex items-center text-gray-500 mb-4">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span><?= htmlspecialchars($event['event_date']) ?></span>
            </div>

            <div class="flex gap-2">
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
      <p class="text-gray-500 mb-4">🔍</p>
      <p class="text-gray-600">Tidak ada event yang ditemukan</p>
      <button onclick="window.location.href='search.php'" 
              class="mt-4 py-2 px-6 bg-[#2DCC70] hover:bg-[#25b562] text-white rounded-lg transition-colors">
        Reset Filter
      </button>
    </div>
  <?php endif; ?>
</main>

<!-- Bottom Navigation (Sama seperti beranda.php) -->
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

<!-- Toast Container -->
<div id="toast-container" class="fixed bottom-4 right-4 space-y-2 z-50"></div>

<script>
// Toggle filter form on mobile
function toggleFilter() {
  const form = document.getElementById('filterForm');
  if (form.classList.contains('hidden')) {
    form.classList.remove('hidden');
  } else {
    form.classList.add('hidden');
  }
}

// Show toast notification
function showToast(message, type = 'success', duration = 3000) {
  const toastContainer = document.getElementById('toast-container');
  const toast = document.createElement('div');
  
  toast.className = `toast px-4 py-2 rounded-lg shadow-md text-white ${
    type === 'success' ? 'bg-green-500' : 'bg-red-500'
  }`;
  toast.textContent = message;
  
  toastContainer.appendChild(toast);
  
  // Remove toast after duration
  setTimeout(() => {
    toast.remove();
  }, duration);
}

// Initialize - hide filter form on mobile by default
document.addEventListener('DOMContentLoaded', () => {
  if (window.innerWidth < 768) {
    document.getElementById('filterForm').classList.add('hidden');
  }
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