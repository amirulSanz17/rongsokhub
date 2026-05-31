<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$userId = $_SESSION['user_id'];

// Delete item
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $userId]);
    header('Location: items.php?msg=deleted');
    exit();
}

// Get user's items
$stmt = $pdo->prepare("
    SELECT i.*, c.nama_kategori 
    FROM items i 
    JOIN categories c ON i.category_id = c.id 
    WHERE i.user_id = ? 
    ORDER BY i.created_at DESC
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Warga RongsokHub</title>
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
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    <a href="items.php" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg"><i class="fas fa-box"></i><span>Data Barang</span></a>
                    <a href="add_item.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-plus-circle"></i><span>Tambah Barang</span></a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-store"></i><span>Daftar Pengepul</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-truck"></i><span>Pengajuan Saya</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Data Barang Saya</h1>
                    <a href="add_item.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus"></i> Tambah Barang
                    </a>
                </div>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Barang berhasil dihapus!</div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($items as $item): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <?php
                        $photoStmt = $pdo->prepare("SELECT foto FROM item_photos WHERE item_id = ? LIMIT 1");
                        $photoStmt->execute([$item['id']]);
                        $photo = $photoStmt->fetch();
                        ?>
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <?php if($photo): ?>
                                <img src="../assets/uploads/<?= $photo['foto'] ?>" alt="<?= $item['nama_barang'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-image text-6xl text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-bold"><?= htmlspecialchars($item['nama_barang']) ?></h3>
                            <p class="text-gray-600 text-sm">Kategori: <?= $item['nama_kategori'] ?></p>
                            <p class="text-gray-600 text-sm">Berat: <?= $item['berat'] ?> kg</p>
                            <p class="text-gray-600 text-sm">Lokasi: <?= htmlspecialchars($item['alamat']) ?></p>
                            <div class="mt-3 flex justify-between items-center">
                                <span class="px-2 py-1 rounded text-xs <?= $item['status'] == 'tersedia' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= str_replace('_', ' ', ucfirst($item['status'])) ?>
                                </span>
                                <a href="?delete=<?= $item['id'] ?>" onclick="return confirm('Yakin hapus barang ini?')" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($items) == 0): ?>
                    <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
                        <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Belum ada barang yang ditambahkan</p>
                        <a href="add_item.php" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg">Tambah Barang Sekarang</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>