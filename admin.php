<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Mengambil data event untuk ditampilkan
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_event'])) {
    // Ambil data dari form
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_description = $_POST['event_description'];
    $event_type = $_POST['event_type'];
    $event_link = $_POST['event_link'];

    // Cek apakah ada file poster
    if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] == 0) {
        $file_tmp = $_FILES['event_poster']['tmp_name'];
        $file_name = $_FILES['event_poster']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array(strtolower($file_ext), $allowed_ext)) {
            $new_file_name = 'poster_' . time() . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, event_description, event_poster, event_type, event_link) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$event_name, $event_date, $event_description, $upload_path, $event_type, $event_link]);
                header("Location: admin.php");
                exit;
            } else {
                echo "Gagal mengunggah gambar.";
                exit;
            }
        } else {
            echo "Format gambar tidak valid. Hanya JPG dan PNG yang diterima.";
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, event_description, event_type, event_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$event_name, $event_date, $event_description, $event_type, $event_link]);
        header("Location: admin.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Admin - CRUD Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Admin - Daftar Event</h1>
            <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">Logout</a>
        </div>

        <!-- Modal Edit Event -->
        <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Edit Event</h2>
                <form id="editForm" method="POST" enctype="multipart/form-data" action="update_event.php" class="space-y-4">
                    <input type="hidden" name="id_event" id="edit_id_event">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Event:</label>
                        <input type="text" name="event_name" id="edit_event_name" required class="w-full px-3 py-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Event:</label>
                        <input type="date" name="event_date" id="edit_event_date" required class="w-full px-3 py-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Event:</label>
                        <select name="event_location" id="edit_event_location" required class="w-full px-3 py-2 border rounded-md">
                            <option value="Online">Online</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Event:</label>
                        <select name="event_type" id="edit_event_type" required class="w-full px-3 py-2 border rounded-md">
                            <option value="Bootcamp">Bootcamp</option>
                            <option value="Webinar">Webinar</option>
                            <option value="Olimpiade">Olimpiade</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Event:</label>
                        <select name="event_fee" id="edit_event_fee" required class="w-full px-3 py-2 border rounded-md">
                            <option value="Gratis">Gratis</option>
                            <option value="Bayar">Bayar</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Event:</label>
                        <textarea name="event_description" id="edit_event_description" required class="w-full px-3 py-2 border rounded-md"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Poster Baru (optional):</label>
                        <input type="file" name="event_poster" accept="image/jpeg, image/png" class="w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Event (opsional):</label>
                        <input type="url" name="event_link" id="edit_event_link" placeholder="Masukkan link URL jika ada" class="w-full px-3 py-2 border rounded-md">
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">Simpan Perubahan</button>
                        <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form Tambah Event -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Tambah Event Baru</h2>
            <form action="admin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Event:</label>
                        <input type="text" name="event_name" required class="w-full px-3 py-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Event:</label>
                        <input type="date" name="event_date" required class="w-full px-3 py-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Event:</label>
                        <select name="event_location" required class="w-full px-3 py-2 border rounded-md">
                            <option value="Online">Online</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Event:</label>
                        <select name="event_fee" required class="w-full px-3 py-2 border rounded-md">
                            <option value="Gratis">Gratis</option>
                            <option value="Bayar">Bayar</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Event:</label>
                        <select name="event_type" required class="w-full px-3 py-2 border rounded-md">
                            <option value="Bootcamp">Bootcamp</option>
                            <option value="Webinar">Webinar</option>
                            <option value="Olimpiade">Olimpiade</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Event (opsional):</label>
                        <input type="url" name="event_link" placeholder="Masukkan link URL jika ada" class="w-full px-3 py-2 border rounded-md">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Event:</label>
                    <textarea name="event_description" required class="w-full px-3 py-2 border rounded-md"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto/Posternya (JPG/PNG):</label>
                    <input type="file" name="event_poster" accept="image/jpeg, image/png" required class="w-full">
                </div>

                <button type="submit" name="submit_event" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">Tambah Event</button>
            </form>
        </div>

        <!-- Daftar Event -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Daftar Event yang Ada</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poster</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($event['event_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($event['event_date']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($event['event_location']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($event['event_fee']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($event['event_type']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="<?= htmlspecialchars($event['event_poster']) ?>" alt="Poster Event" class="w-20 h-auto">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($event['event_link'])): ?>
                                    <a href="<?= htmlspecialchars($event['event_link']) ?>" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <button onclick="openEditModal(<?= $event['id_event'] ?>)" class="text-blue-600 hover:text-blue-800">Edit</button>
                                <a href="delete_event.php?id=<?= $event['id_event'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus event ini?')" class="text-red-600 hover:text-red-800">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(id_event) {
        <?php foreach ($events as $e): ?>
            if (id_event == <?= $e['id_event'] ?>) {
                document.getElementById('edit_id_event').value = '<?= $e['id_event'] ?>';
                document.getElementById('edit_event_name').value = '<?= htmlspecialchars($e['event_name'], ENT_QUOTES) ?>';
                document.getElementById('edit_event_location').value = '<?= $e['event_location'] ?>';
                document.getElementById('edit_event_fee').value = '<?= $e['event_fee'] ?>';
                document.getElementById('edit_event_date').value = '<?= $e['event_date'] ?>';
                document.getElementById('edit_event_link').value = '<?= $e['event_link'] ?>';
                document.getElementById('edit_event_description').value = `<?= htmlspecialchars($e['event_description'], ENT_QUOTES) ?>`;
                document.getElementById('edit_event_type').value = '<?= $e['event_type'] ?>';
            }
        <?php endforeach; ?>

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    </script>
</body>
</html>