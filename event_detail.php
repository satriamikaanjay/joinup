<?php
session_start();
include 'koneksi.php';

// Ambil data profil user (sama seperti halaman lainnya)
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

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Ambil data event berdasarkan ID
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id_event = :id");
    $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch();

    if (!$event) {
        echo "Event tidak ditemukan.";
        exit;
    }
} else {
    echo "ID event tidak diberikan.";
    exit;
}

// Menghitung status berdasarkan tanggal
$today     = new DateTime('today');                       // 00:00 hari ini
$eventDate = new DateTime($event['event_date']);          // 00:00 tanggal event

if ($eventDate < $today) {
    // Kalau tanggal event sudah lewat
    $event_status = 'Event sudah selesai';
    $status_color = 'bg-gray-500 text-white';
} elseif ($eventDate == $today) {
    // Kalau tanggal event sama dengan hari ini
    $event_status = 'Event sedang berlangsung';
    $status_color = 'bg-yellow-500 text-white';
} else {
    // Hitung selisih hari penuh
    $interval  = $today->diff($eventDate);
    $days_left = $interval->days;                          // integer hari

    $event_status = "Event akan dimulai dalam $days_left hari";
    $status_color = 'bg-green-500 text-white';
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
  <title><?= htmlspecialchars($event['event_name']) ?> - Detail Event</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">


<!-- Header (Sama seperti halaman lainnya) -->
<?php
include 'navbar.php';
?>

<!-- Main Content -->
<main class="flex-grow container mx-auto px-4 py-8">
  <div class="max-w-4xl mx-auto">
    <!-- Judul dan Status -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
      <h1 class="text-3xl font-bold text-[#183727]"><?= htmlspecialchars($event['event_name']) ?></h1>
      <span class="px-3 py-1 rounded-full text-sm <?= $status_color ?> mt-2 md:mt-0">
        <?= $event_status ?>
      </span>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Poster Event -->
      <div class="lg:w-1/2">
        <div class="aspect-[4/3] rounded-xl overflow-hidden shadow-lg">
          <img src="<?= htmlspecialchars($event['event_poster'] ?? 'uploads/default-event.jpg') ?>" 
               alt="Poster Event" 
               class="w-full h-full object-cover">
        </div>
      </div>

      <!-- Detail Event -->
      <div class="lg:w-1/2 bg-white rounded-xl shadow-lg p-6">
        <div class="space-y-4">
          <!-- Informasi Dasar -->
          <div class="flex items-start">
            <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-700">Tanggal Event</h3>
              <p class="text-gray-600"><?= htmlspecialchars($event['event_date']) ?></p>
            </div>
          </div>

          <div class="flex items-start">
            <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-700">Lokasi</h3>
              <p class="text-gray-600"><?= htmlspecialchars($event['event_location']) ?></p>
            </div>
          </div>

          <div class="flex items-start">
            <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-700">Biaya</h3>
              <p class="text-gray-600"><?= htmlspecialchars($event['event_fee']) ?></p>
            </div>
          </div>

          <div class="flex items-start">
            <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <div>
              <h3 class="font-semibold text-gray-700">Jenis Event</h3>
              <p class="text-gray-600"><?= htmlspecialchars($event['event_type']) ?></p>
            </div>
          </div>

          <!-- Deskripsi -->
          <div class="pt-4 border-t border-gray-200">
            <h3 class="font-semibold text-lg text-gray-800 mb-2">Deskripsi Event</h3>
            <p class="text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($event['event_description'])) ?></p>
          </div>

          <!-- Tombol Aksi -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <a href="<?= htmlspecialchars($event['event_link'] ?? '#') ?>" 
               class="flex-1 py-3 px-6 bg-[#2DCC70] hover:bg-[#25b562] text-white text-center rounded-lg font-medium transition-colors">
              Daftar Sekarang
            </a>
            <button onclick="window.history.back()" 
                    class="flex-1 py-3 px-6 border-2 border-[#2DCC70] text-[#183727] hover:bg-[#2DCC70]/10 rounded-lg transition-colors">
              Kembali
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Bottom Navigation (Sama seperti halaman lainnya) -->
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