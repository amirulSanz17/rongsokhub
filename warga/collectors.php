<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$userId = $_SESSION['user_id'];

// Get all collectors with profiles
$stmt = $pdo->query("
    SELECT u.*, cp.nama_usaha, cp.alamat, cp.area_operasional, cp.deskripsi, cp.foto,
           (SELECT COUNT(*) FROM pickup_requests WHERE collector_id = u.id AND status = 'completed') as total_transactions
    FROM users u
    LEFT JOIN collector_profiles cp ON u.id = cp.user_id
    WHERE u.role = 'pengepul'
    ORDER BY total_transactions DESC
");
$collectors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengepul - Warga RongsokHub</title>
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
                    <a href="items.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-box"></i><span>Data Barang</span></a>
                    <a href="add_item.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-plus-circle"></i><span>Tambah Barang</span></a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg"><i class="fas fa-store"></i><span>Daftar Pengepul</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-truck"></i><span>Pengajuan Saya</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Daftar Pengepul Terpercaya</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach($collectors as $collector): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($collector['nama_usaha'] ?: 'Pengepul RongsokHub') ?></h3>
                                    <p class="text-gray-600 mt-1">Pemilik: <?= htmlspecialchars($collector['nama']) ?></p>
                                    <p class="text-gray-600">📞 <?= htmlspecialchars($collector['phone'] ?: '-') ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center text-yellow-500">
                                        <i class="fas fa-star"></i>
                                        <span class="ml-1 text-gray-700"><?= number_format($collector['total_transactions'] > 0 ? 4.5 : 0, 1) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-500"><?= $collector['total_transactions'] ?> transaksi</p>
                                </div>
                            </div>
                            
                            <div class="mt-3 p-3 bg-gray-50 rounded">
                                <p class="text-sm"><i class="fas fa-map-marker-alt"></i> <strong>Alamat:</strong> <?= htmlspecialchars($collector['alamat'] ?: 'Belum tersedia') ?></p>
                                <p class="text-sm mt-1"><i class="fas fa-city"></i> <strong>Area Operasional:</strong> <?= htmlspecialchars($collector['area_operasional'] ?: 'Belum tersedia') ?></p>
                                <p class="text-sm mt-1"><i class="fas fa-info-circle"></i> <strong>Deskripsi:</strong> <?= htmlspecialchars($collector['deskripsi'] ?: 'Belum tersedia') ?></p>
                            </div>
                            
                            <div class="mt-4">
                                <a href="select_item_for_collector.php?collector_id=<?= $collector['id'] ?>" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-truck"></i> Ajukan Barang
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>