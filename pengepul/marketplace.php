<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('pengepul');

$collectorId = $_SESSION['user_id'];

// Get available items
$stmt = $pdo->query("
    SELECT i.*, c.nama_kategori, u.nama as warga_nama, u.phone as warga_phone,
           (SELECT foto FROM item_photos WHERE item_id = i.id LIMIT 1) as foto
    FROM items i
    JOIN categories c ON i.category_id = c.id
    JOIN users u ON i.user_id = u.id
    WHERE i.status = 'tersedia'
    ORDER BY i.created_at DESC
");
$items = $stmt->fetchAll();

// Submit pickup request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['take_item'])) {
    $itemId = $_POST['item_id'];
    $wargaId = $_POST['warga_id'];
    
    // Check if request already exists
    $check = $pdo->prepare("SELECT * FROM pickup_requests WHERE item_id = ? AND collector_id = ? AND status != 'rejected'");
    $check->execute([$itemId, $collectorId]);
    
    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO pickup_requests (item_id, warga_id, collector_id, request_by, status) VALUES (?, ?, ?, 'pengepul', 'pending')");
        $stmt->execute([$itemId, $wargaId, $collectorId]);
        
        // Update item status
        $stmt2 = $pdo->prepare("UPDATE items SET status = 'menunggu_konfirmasi' WHERE id = ?");
        $stmt2->execute([$itemId]);
        
        $success = "Pengajuan berhasil dikirim! Menunggu konfirmasi warga.";
    } else {
        $error = "Anda sudah pernah mengajukan pengambilan untuk barang ini!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Barang - Pengepul RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="w-64 bg-orange-800 text-white fixed h-full">
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-recycle text-2xl"></i>
                    <span class="text-xl font-bold">RongsokHub</span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    <a href="marketplace.php" class="flex items-center space-x-3 p-3 bg-orange-700 rounded-lg"><i class="fas fa-store"></i><span>Marketplace Barang</span></a>
                    <a href="incoming.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-inbox"></i><span>Pengajuan Masuk</span></a>
                    <a href="pickup.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-truck"></i><span>Barang Dijemput</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Marketplace Barang Rongsok</h1>
                
                <?php if(isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($items as $item): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <?php if($item['foto']): ?>
                                <img src="../assets/uploads/<?= $item['foto'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-image text-6xl text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-bold"><?= htmlspecialchars($item['nama_barang']) ?></h3>
                            <p class="text-gray-600 text-sm">Kategori: <?= $item['nama_kategori'] ?></p>
                            <p class="text-gray-600 text-sm">Berat: <?= $item['berat'] ?> kg</p>
                            <p class="text-gray-600 text-sm">Lokasi: <?= htmlspecialchars($item['alamat']) ?></p>
                            <p class="text-gray-600 text-sm">Pemilik: <?= htmlspecialchars($item['warga_nama']) ?></p>
                            <p class="text-gray-600 text-sm mt-2"><?= htmlspecialchars(substr($item['deskripsi'], 0, 100)) ?>...</p>
                            
                            <form method="POST" class="mt-4">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="warga_id" value="<?= $item['user_id'] ?>">
                                <button type="submit" name="take_item" class="w-full bg-orange-600 text-white py-2 rounded-lg hover:bg-orange-700">
                                    <i class="fas fa-truck"></i> Ambil Barang
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($items) == 0): ?>
                    <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
                        <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Belum ada barang rongsok yang tersedia</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>