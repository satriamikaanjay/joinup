<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
    $profile_picture = 'uploads/default.jpg'; // User belum login, langsung pakai default
  }

// Ambil event yang sudah ditandai oleh user
$stmt = $pdo->prepare("
    SELECT e.* 
    FROM events e
    JOIN marked_events m ON e.id_event = m.event_id
    WHERE m.user_id = ?
    ORDER BY e.event_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$marked_events = $stmt->fetchAll();

$current = basename($_SERVER['PHP_SELF']); 
// Sisanya mirip dengan beranda.php, sesuaikan dengan tampilan yang diinginkan
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Marked Events</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="icon" href="aset/bulat.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">




<!-- Header -->
<?php
include 'navbar.php';
?>

<!-- Main Content -->
<main class="flex-grow container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-8 text-center text-[#183727]">Event yang Ditandai</h1>

  <?php if (count($marked_events) > 0): ?>
    <div class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($marked_events as $event): ?>
        <!-- Tampilkan event yang sudah ditandai -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
          <div class="aspect-video overflow-hidden">
            <img src="<?= htmlspecialchars($event['event_poster']) ?>" alt="Poster Event" 
                 class="w-full h-full object-cover transform hover:scale-105 transition duration-300">
          </div>
          
          <div class="p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($event['event_name']) ?></h2>
            
            <div class="flex items-center text-gray-500 mb-4">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span><?= htmlspecialchars($event['event_date']) ?></span>
            </div>

            <button onclick="window.location.href='event_detail.php?id=<?= $event['id_event'] ?>'" 
                    class="w-full py-2 px-4 border-2 border-[#2DCC70] text-[#183727] hover:bg-[#2DCC70]/10 rounded-lg transition-colors">
              Detail Event
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-12">
      <p class="text-gray-500 mb-4">📌</p>
      <p class="text-gray-600">Anda belum menandai event apapun</p>
      <button onclick="window.location.href='index.php'" 
              class="mt-4 py-2 px-6 bg-[#2DCC70] hover:bg-[#25b562] text-white rounded-lg transition-colors">
        Cari Event
      </button>
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