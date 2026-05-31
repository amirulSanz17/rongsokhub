<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('pengepul');

$collectorId = $_SESSION['user_id'];

// Get completed requests history
$stmt = $pdo->prepare("
    SELECT pr.*, 
           i.nama_barang, i.berat, i.alamat,
           u.nama as warga_nama, u.phone as warga_phone
    FROM pickup_requests pr
    JOIN items i ON pr.item_id = i.id
    JOIN users u ON pr.warga_id = u.id
    WHERE pr.collector_id = ? AND pr.status = 'completed'
    ORDER BY pr.created_at DESC
");
$stmt->execute([$collectorId]);
$history = $stmt->fetchAll();

// Calculate total weight collected
$totalWeight = array_sum(array_column($history, 'berat'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - Pengepul RongsokHub</title>
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
                    <a href="marketplace.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-store"></i><span>Marketplace Barang</span></a>
                    <a href="incoming.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-inbox"></i><span>Pengajuan Masuk</span></a>
                    <a href="pickup.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-truck"></i><span>Barang Dijemput</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 bg-orange-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Riwayat Penjemputan</h1>
                
                <!-- Summary Card -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-lg shadow p-6 text-white mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm">Total Transaksi Selesai</p>
                            <p class="text-3xl font-bold"><?= count($history) ?> Kali</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm">Total Berat Dikumpulkan</p>
                            <p class="text-3xl font-bold"><?= number_format($totalWeight, 0) ?> kg</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-6 py-3">Tanggal Selesai</th>
                                <th class="px-6 py-3">Barang</th>
                                <th class="px-6 py-3">Berat</th>
                                <th class="px-6 py-3">Pemilik</th>
                                <th class="px-6 py-3">Alamat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($history as $item): ?>
                            <tr>
                                <td class="px-6 py-4"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td class="px-6 py-4"><?= $item['berat'] ?> kg</td>
                                <td class="px-6 py-4"><?= htmlspecialchars($item['warga_nama']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($item['alamat']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(count($history) == 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-history text-4xl mb-2"></i>
                                    <p>Belum ada riwayat penjemputan</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>