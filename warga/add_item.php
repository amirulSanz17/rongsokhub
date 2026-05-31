<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$categories = $pdo->query("SELECT * FROM categories ORDER BY nama_kategori")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $categoryId = $_POST['category_id'] ?? '';
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $berat = $_POST['berat'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($categoryId) || empty($namaBarang) || empty($berat) || empty($alamat)) {
        $error = 'Lengkapi semua field wajib terlebih dahulu.';
    } elseif (!is_numeric($berat) || $berat <= 0) {
        $error = 'Berat harus berupa angka positif.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO items (user_id, category_id, nama_barang, berat, alamat, deskripsi, status) VALUES (?, ?, ?, ?, ?, ?, 'tersedia')");
        $stmt->execute([$userId, $categoryId, $namaBarang, $berat, $alamat, $deskripsi]);
        $itemId = $pdo->lastInsertId();

        $uploadDir = __DIR__ . '/../assets/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!empty($_FILES['photos']['tmp_name'])) {
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if ($key >= 5) {
                    break;
                }
                if (!empty($tmp_name) && is_uploaded_file($tmp_name) && $_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = basename($_FILES['photos']['name'][$key]);
                    $safeName = time() . '_' . $key . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
                    $filePath = $uploadDir . $safeName;

                    if (move_uploaded_file($tmp_name, $filePath)) {
                        $stmt = $pdo->prepare("INSERT INTO item_photos (item_id, foto) VALUES (?, ?)");
                        $stmt->execute([$itemId, $safeName]);
                    }
                }
            }
        }

        header('Location: /rongsokhub/warga/items.php?msg=added');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - Warga RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="w-64 bg-blue-800 text-white fixed h-full">
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-recycle text-2xl"></i>
                    <span class="text-xl font-bold">RongsokHub</span>
                </div>
                <div class="mb-6 p-3 bg-blue-700 rounded-lg">
                    <p class="text-sm">Halo,</p>
                    <p class="font-bold"><?= $_SESSION['nama'] ?></p>
                </div>
                <nav class="space-y-2">
                    <a href="/rongsokhub/warga/dashboard.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    <a href="/rongsokhub/warga/items.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-box"></i><span>Data Barang</span></a>
                    <a href="/rongsokhub/warga/add_item.php" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg"><i class="fas fa-plus-circle"></i><span>Tambah Barang</span></a>
                    <a href="/rongsokhub/warga/collectors.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-store"></i><span>Daftar Pengepul</span></a>
                    <a href="/rongsokhub/warga/requests.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-truck"></i><span>Pengajuan Saya</span></a>
                    <a href="/rongsokhub/warga/history.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="/rongsokhub/logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Tambah Barang Rongsok</h1>
                
                <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Nama Barang</label>
                            <input type="text" name="nama_barang" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Kategori</label>
                            <select name="category_id" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Berat Estimasi (kg)</label>
                            <input type="number" step="0.01" name="berat" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Alamat Lengkap</label>
                            <textarea name="alamat" required rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Deskripsi Barang</label>
                            <textarea name="deskripsi" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan kondisi barang, jenis, dll"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 mb-2">Foto Barang (Minimal 1, Maksimal 5)</label>
                            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/jpg" multiple required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Format: JPG, PNG. Maksimal 5 foto.</p>
                        </div>
                        
                        <div class="flex gap-4 pt-4">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>Simpan Barang
                            </button>
                            <a href="/rongsokhub/warga/items.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>