<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('pengepul');

$collectorId = $_SESSION['user_id'];

// Update pickup status
if (isset($_POST['update_pickup'])) {
    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE pickup_requests SET status = ? WHERE id = ? AND collector_id = ?");
    $stmt->execute([$status, $requestId, $collectorId]);
    
    if ($status == 'pickup') {
        $itemStatus = 'diproses';
    } elseif ($status == 'completed') {
        $itemStatus = 'selesai';
    }

    if (!empty($itemStatus)) {
        $stmt2 = $pdo->prepare("\
            UPDATE items SET status = ? \
            WHERE id = (SELECT item_id FROM pickup_requests WHERE id = ?)
        ");
        $stmt2->execute([$itemStatus, $requestId]);
    }
    
    header('Location: pickup.php');
    exit();
}

// Get accepted/pickup requests
$stmt = $pdo->prepare("
    SELECT pr.*, 
           i.nama_barang, i.berat, i.alamat,
           u.nama as warga_nama, u.phone as warga_phone
    FROM pickup_requests pr
    JOIN items i ON pr.item_id = i.id
    JOIN users u ON pr.warga_id = u.id
    WHERE pr.collector_id = ? AND pr.status IN ('accepted', 'pickup')
    ORDER BY 
        CASE WHEN pr.status = 'pickup' THEN 1 ELSE 2 END,
        pr.created_at ASC
");
$stmt->execute([$collectorId]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Dijemput - Pengepul RongsokHub</title>
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
                    <a href="pickup.php" class="flex items-center space-x-3 p-3 bg-orange-700 rounded-lg"><i class="fas fa-truck"></i><span>Barang Dijemput</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Penjemputan</h1>
                
                <div class="space-y-4">
                    <?php foreach($requests as $req): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold"><?= htmlspecialchars($req['nama_barang']) ?></h3>
                                <p class="text-gray-600">Berat: <?= $req['berat'] ?> kg</p>
                                <p class="text-gray-600">📍 <?= htmlspecialchars($req['alamat']) ?></p>
                                <p class="text-gray-600">👤 <?= htmlspecialchars($req['warga_nama']) ?> - 📞 <?= $req['warga_phone'] ?></p>
                            </div>
                            <div>
                                <span class="px-3 py-1 rounded-full text-sm <?= $req['status'] == 'pickup' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $req['status'] == 'pickup' ? 'Dalam Perjalanan' : 'Menunggu Penjemputan' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-3 justify-end">
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <?php if($req['status'] == 'accepted'): ?>
                                    <button type="submit" name="update_pickup" value="1" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                        <input type="hidden" name="status" value="pickup">
                                        <i class="fas fa-truck"></i> Mulai Perjalanan
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="update_pickup" value="1" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                                        <input type="hidden" name="status" value="completed">
                                        <i class="fas fa-check-double"></i> Selesaikan Penjemputan
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($requests) == 0): ?>
                    <div class="bg-white rounded-lg shadow p-12 text-center">
                        <i class="fas fa-check-circle text-6xl text-green-400 mb-4"></i>
                        <p class="text-gray-600">Tidak ada barang yang perlu dijemput</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>