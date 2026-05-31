<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$userId = $_SESSION['user_id'];

// Get completed requests history
$stmt = $pdo->prepare("
    SELECT pr.*, 
           i.nama_barang, i.berat,
           c.nama as pengepul_nama,
           cp.nama_usaha
    FROM pickup_requests pr
    JOIN items i ON pr.item_id = i.id
    JOIN users c ON pr.collector_id = c.id
    LEFT JOIN collector_profiles cp ON c.id = cp.user_id
    WHERE pr.warga_id = ? AND pr.status = 'completed'
    ORDER BY pr.created_at DESC
");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - Warga RongsokHub</title>
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
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-store"></i><span>Daftar Pengepul</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-truck"></i><span>Pengajuan Saya</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Riwayat Penjemputan</h1>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Barang</th>
                                <th class="px-6 py-3">Berat</th>
                                <th class="px-6 py-3">Pengepul</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($history as $item): ?>
                            <tr>
                                <td class="px-6 py-4"><?= date('d/m/Y', strtotime($item['created_at'])) ?></tr>
                                <td class="px-6 py-4"><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td class="px-6 py-4"><?= $item['berat'] ?> kg</td>
                                <td class="px-6 py-4"><?= htmlspecialchars($item['nama_usaha'] ?: $item['pengepul_nama']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                                        Selesai
                                    </span>
                                </td>
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