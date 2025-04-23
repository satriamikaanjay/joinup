<header class="bg-white shadow-sm sticky top-0 z-40">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center">
  <div class="flex items-center space-x-2">
    <img src="aset/logo.png" alt="Logo" class="w-12">
</div>
<img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile" 
     class="w-10 h-10 rounded-full border-2 border-[#2DCC70] cursor-pointer"
     onclick="window.location.href='profile.php'"
     style="object-fit: cover; object-position: center; width: 40px; height: 40px;">


  </div>
</header>