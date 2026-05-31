<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$userId = $_SESSION['user_id'];

// Get user's requests
$stmt = $pdo->prepare("
    SELECT pr.*, 
           i.nama_barang, i.berat, i.alamat as item_alamat,
           c.nama as pengepul_nama,
           cp.nama_usaha,
           (SELECT foto FROM item_photos WHERE item_id = i.id LIMIT 1) as foto
    FROM pickup_requests pr
    JOIN items i ON pr.item_id = i.id
    JOIN users c ON pr.collector_id = c.id
    LEFT JOIN collector_profiles cp ON c.id = cp.user_id
    WHERE pr.warga_id = ?
    ORDER BY pr.created_at DESC
");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();

// Cancel request
if (isset($_GET['cancel'])) {
    $stmt = $pdo->prepare("SELECT * FROM pickup_requests WHERE id = ? AND warga_id = ? AND status = 'pending'");
    $stmt->execute([$_GET['cancel'], $userId]);
    $request = $stmt->fetch();

    if ($request) {
        $stmt = $pdo->prepare("UPDATE pickup_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$_GET['cancel']]);

        $stmt2 = $pdo->prepare("UPDATE items SET status = 'tersedia' WHERE id = ?");
        $stmt2->execute([$request['item_id']]);
    }

    header('Location: requests.php?msg=cancelled');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Saya - Warga RongsokHub</title>
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
                    <a href="requests.php" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg"><i class="fas fa-truck"></i><span>Pengajuan Saya</span></a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg"><i class="fas fa-history"></i><span>Riwayat</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Pengajuan Penjemputan Saya</h1>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Pengajuan berhasil dibatalkan!</div>
                <?php endif; ?>
                
                <div class="space-y-4">
                    <?php foreach($requests as $req): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="w-24 h-24 bg-gray-200 rounded flex items-center justify-center">
                                    <?php if($req['foto']): ?>
                                        <img src="../assets/uploads/<?= $req['foto'] ?>" class="w-full h-full object-cover rounded">
                                    <?php else: ?>
                                        <i class="fas fa-image text-3xl text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold"><?= htmlspecialchars($req['nama_barang']) ?></h3>
                                    <p class="text-gray-600">Berat: <?= $req['berat'] ?> kg</p>
                                    <p class="text-gray-600">Pengepul: <?= htmlspecialchars($req['nama_usaha'] ?: $req['pengepul_nama']) ?></p>
                                    <p class="text-gray-600">Tanggal: <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="mb-2">
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'accepted' => 'bg-blue-100 text-blue-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'pickup' => 'bg-purple-100 text-purple-800',
                                        'completed' => 'bg-green-100 text-green-800'
                                    ];
                                    $statusText = [
                                        'pending' => 'Menunggu Konfirmasi',
                                        'accepted' => 'Diterima',
                                        'rejected' => 'Ditolak',
                                        'pickup' => 'Dalam Perjalanan',
                                        'completed' => 'Selesai'
                                    ];
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-sm <?= $statusColors[$req['status']] ?>">
                                        <?= $statusText[$req['status']] ?>
                                    </span>
                                </div>
                                <?php if($req['status'] == 'pending'): ?>
                                    <a href="?cancel=<?= $req['id'] ?>" onclick="return confirm('Yakin batalkan pengajuan ini?')" class="text-red-600 hover:text-red-800 text-sm">
                                        <i class="fas fa-times"></i> Batalkan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if($req['status'] == 'accepted'): ?>
                        <div class="mt-4 p-3 bg-green-50 rounded">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-circle"></i> Pengajuan Anda telah diterima. Pengepul akan segera menghubungi Anda untuk penjadwalan penjemputan.
                            </p>
                        </div>
                        <?php elseif($req['status'] == 'pickup'): ?>
                        <div class="mt-4 p-3 bg-blue-50 rounded">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-truck"></i> Pengepul sedang dalam perjalanan menuju lokasi Anda.
                            </p>
                        </div>
                        <?php elseif($req['status'] == 'completed'): ?>
                        <div class="mt-4 p-3 bg-green-50 rounded">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-double"></i> Penjemputan telah selesai. Terima kasih telah berkontribusi mengurangi sampah!
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($requests) == 0): ?>
                    <div class="bg-white rounded-lg shadow p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Belum ada pengajuan penjemputan</p>
                        <a href="collectors.php" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg">Ajukan Sekarang</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>