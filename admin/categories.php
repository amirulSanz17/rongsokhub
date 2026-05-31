<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('admin');

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO categories (nama_kategori) VALUES (?)");
    $stmt->execute([$_POST['nama_kategori']]);
    header('Location: categories.php?msg=added');
    exit();
}

// Delete category
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: categories.php?msg=deleted');
    exit();
}

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="w-64 bg-green-800 text-white fixed h-full">
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-recycle text-2xl"></i>
                    <span class="text-xl font-bold">RongsokHub</span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    <a href="users.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-users"></i><span>Kelola User</span></a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-store"></i><span>Kelola Pengepul</span></a>
                    <a href="categories.php" class="flex items-center space-x-3 p-3 bg-green-700 rounded-lg"><i class="fas fa-tags"></i><span>Kelola Kategori</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-truck"></i><span>Kelola Pengajuan</span></a>
                    <a href="reports.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Kategori Barang</h1>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= $_GET['msg'] == 'added' ? 'Kategori berhasil ditambahkan!' : 'Kategori berhasil dihapus!' ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Category Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Tambah Kategori Baru</h2>
                    <form method="POST" class="flex gap-4">
                        <input type="text" name="nama_kategori" required placeholder="Nama Kategori" class="flex-1 px-4 py-2 border rounded-lg">
                        <button type="submit" name="add" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Tambah</button>
                    </form>
                </div>
                
                <!-- Categories List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Nama Kategori</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td class="px-6 py-4"><?= $cat['id'] ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($cat['nama_kategori']) ?></td>
                                <td class="px-6 py-4">
                                    <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('Yakin hapus kategori ini?')" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>