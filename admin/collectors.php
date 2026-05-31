<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('admin');

// Get all collectors with their profiles
$stmt = $pdo->query("
    SELECT u.*, cp.nama_usaha, cp.alamat, cp.area_operasional, cp.deskripsi 
    FROM users u 
    LEFT JOIN collector_profiles cp ON u.id = cp.user_id 
    WHERE u.role = 'pengepul' 
    ORDER BY u.created_at DESC
");
$collectors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengepul - Admin RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="w-64 bg-green-800 text-white">
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-recycle text-2xl"></i>
                    <span class="text-xl font-bold">RongsokHub</span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    <a href="users.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-users"></i><span>Kelola User</span></a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 bg-green-700 rounded-lg"><i class="fas fa-store"></i><span>Kelola Pengepul</span></a>
                    <a href="categories.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-tags"></i><span>Kelola Kategori</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-truck"></i><span>Kelola Pengajuan</span></a>
                    <a href="reports.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Data Pengepul</h1>
                
                <div class="grid gap-6">
                    <?php foreach($collectors as $collector): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($collector['nama']) ?></h3>
                                <p class="text-gray-600"><?= htmlspecialchars($collector['email']) ?></p>
                                <p class="text-gray-600">📞 <?= htmlspecialchars($collector['phone']) ?></p>
                                <?php if($collector['nama_usaha']): ?>
                                <div class="mt-3 p-3 bg-gray-50 rounded">
                                    <p class="font-semibold">🏢 <?= htmlspecialchars($collector['nama_usaha']) ?></p>
                                    <p class="text-sm text-gray-600">📍 <?= htmlspecialchars($collector['alamat']) ?></p>
                                    <p class="text-sm text-gray-600">📋 Area: <?= htmlspecialchars($collector['area_operasional']) ?></p>
                                    <p class="text-sm text-gray-600">📝 <?= htmlspecialchars($collector['deskripsi']) ?></p>
                                </div>
                                <?php else: ?>
                                <p class="text-yellow-600 mt-2">⚠️ Profil belum lengkap</p>
                                <?php endif; ?>
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