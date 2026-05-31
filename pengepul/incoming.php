<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('pengepul');

$collectorId = $_SESSION['user_id'];

// Process request (accept/reject)
if (isset($_POST['action'])) {
    $requestId = $_POST['request_id'];
    $status = $_POST['action'] == 'accept' ? 'accepted' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE pickup_requests SET status = ? WHERE id = ? AND collector_id = ?");
    $stmt->execute([$status, $requestId, $collectorId]);
    
    if ($status == 'accepted') {
        // Update item status
        $stmt2 = $pdo->prepare("
            UPDATE items SET status = 'diproses' 
            WHERE id = (SELECT item_id FROM pickup_requests WHERE id = ?)
        ");
        $stmt2->execute([$requestId]);
    }
    
    header('Location: incoming.php');
    exit();
}

// Get incoming requests (warga request to this collector)
$stmt = $pdo->prepare("
    SELECT pr.*, 
           i.nama_barang, i.berat, i.alamat, i.deskripsi,
           u.nama as warga_nama, u.phone as warga_phone,
           (SELECT foto FROM item_photos WHERE item_id = i.id LIMIT 1) as foto
    FROM pickup_requests pr
    JOIN items i ON pr.item_id = i.id
    JOIN users u ON pr.warga_id = u.id
    WHERE pr.collector_id = ? AND pr.request_by = 'warga' AND pr.status = 'pending'
    ORDER BY pr.created_at DESC
");
$stmt->execute([$collectorId]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Masuk - Pengepul RongsokHub</title>
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
                    <a href="incoming.php" class="flex items-center space-x-3 p-3 bg-orange-700 rounded-lg"><i class="fas fa-inbox"></i><span>Pengajuan Masuk</span></a>
                    <a href="pickup.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-truck"></i><span>Barang Dijemput</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Pengajuan Penjemputan Masuk</h1>
                
                <div class="space-y-4">
                    <?php foreach($requests as $req): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex gap-4">
                            <div class="w-32 h-32 bg-gray-200 rounded flex items-center justify-center">
                                <?php if($req['foto']): ?>
                                    <img src="../assets/uploads/<?= $req['foto'] ?>" class="w-full h-full object-cover rounded">
                                <?php else: ?>
                                    <i class="fas fa-image text-4xl text-gray-400"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold"><?= htmlspecialchars($req['nama_barang']) ?></h3>
                                <p class="text-gray-600">Kategori: <?= $req['nama_barang'] ?></p>
                                <p class="text-gray-600">Berat: <?= $req['berat'] ?> kg</p>
                                <p class="text-gray-600">📍 Alamat: <?= htmlspecialchars($req['alamat']) ?></p>
                                <p class="text-gray-600">👤 Pemilik: <?= htmlspecialchars($req['warga_nama']) ?> (📞 <?= $req['warga_phone'] ?>)</p>
                                <p class="text-gray-600 mt-2">📝 <?= htmlspecialchars($req['deskripsi']) ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-3 justify-end">
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <button type="submit" name="action" value="accept" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-check"></i> Terima
                                </button>
                                <button type="submit" name="action" value="reject" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($requests) == 0): ?>
                    <div class="bg-white rounded-lg shadow p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Tidak ada pengajuan masuk</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>