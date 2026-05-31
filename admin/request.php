<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('admin');

// Update request status
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE pickup_requests SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['request_id']]);
    
    $status = $_POST['status'];
    if ($status == 'accepted') {
        $itemStatus = 'diproses';
    } elseif ($status == 'rejected') {
        $itemStatus = 'tersedia';
    } elseif ($status == 'completed') {
        $itemStatus = 'selesai';
    } else {
        $itemStatus = '';
    }

    if ($itemStatus !== '') {
        $stmt2 = $pdo->prepare("\
            UPDATE items SET status = ? \
            WHERE id = (SELECT item_id FROM pickup_requests WHERE id = ?)
        ");
        $stmt2->execute([$itemStatus, $_POST['request_id']]);
    }
    header('Location: requests.php?msg=updated');
    exit();
}

// Get all requests
$stmt = $pdo->query("
    SELECT pr.*, 
           i.nama_barang, i.berat, i.alamat as item_alamat,
           w.nama as warga_nama, w.phone as warga_phone,
           c.nama as pengepul_nama,
           cp.nama_usaha
    FROM pickup_requests pr
    JOIN items i ON pr.item_id = i.id
    JOIN users w ON pr.warga_id = w.id
    JOIN users c ON pr.collector_id = c.id
    LEFT JOIN collector_profiles cp ON c.id = cp.user_id
    ORDER BY pr.created_at DESC
");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengajuan - Admin RongsokHub</title>
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
                    <a href="categories.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-tags"></i><span>Kelola Kategori</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 bg-green-700 rounded-lg"><i class="fas fa-truck"></i><span>Kelola Pengajuan</span></a>
                    <a href="reports.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Pengajuan Penjemputan</h1>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Status pengajuan berhasil diupdate!</div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Barang</th>
                                <th class="px-6 py-3">Warga</th>
                                <th class="px-6 py-3">Pengepul</th>
                                <th class="px-6 py-3">Request By</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($requests as $req): ?>
                            <tr>
                                <td class="px-6 py-4"><?= $req['id'] ?></td>
                                <td class="px-6 py-4">
                                    <strong><?= htmlspecialchars($req['nama_barang']) ?></strong><br>
                                    <small class="text-gray-600"><?= $req['berat'] ?> kg</small>
                                </td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($req['warga_nama']) ?><br>
                                    <small><?= $req['warga_phone'] ?></small>
                                </td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($req['pengepul_nama']) ?><br>
                                    <small><?= htmlspecialchars($req['nama_usaha']) ?></small>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs <?= $req['request_by'] == 'warga' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' ?>">
                                        <?= ucfirst($req['request_by']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="text-sm border rounded px-2 py-1">
                                            <option value="pending" <?= $req['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="accepted" <?= $req['status'] == 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                            <option value="rejected" <?= $req['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                            <option value="pickup" <?= $req['status'] == 'pickup' ? 'selected' : '' ?>>Pickup</option>
                                            <option value="completed" <?= $req['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td class="px-6 py-4"><?= date('d/m/Y', strtotime($req['created_at'])) ?></td>
                                <td class="px-6 py-4">
                                    <button onclick="showDetail(<?= $req['id'] ?>)" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
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