<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('admin');

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalItems = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$totalRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests")->fetchColumn();
$completedRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'completed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-green-800 text-white">
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-recycle text-2xl"></i>
                    <span class="text-xl font-bold">RongsokHub</span>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 bg-green-700 rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="users.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg">
                        <i class="fas fa-users"></i>
                        <span>Kelola User</span>
                    </a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg">
                        <i class="fas fa-store"></i>
                        <span>Kelola Pengepul</span>
                    </a>
                    <a href="categories.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg">
                        <i class="fas fa-tags"></i>
                        <span>Kelola Kategori</span>
                    </a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg">
                        <i class="fas fa-truck"></i>
                        <span>Kelola Pengajuan</span>
                    </a>
                    <a href="reports.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg">
                        <i class="fas fa-chart-line"></i>
                        <span>Laporan</span>
                    </a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h1>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Pengguna</p>
                                <p class="text-3xl font-bold"><?= $totalUsers ?></p>
                            </div>
                            <i class="fas fa-users text-4xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Barang</p>
                                <p class="text-3xl font-bold"><?= $totalItems ?></p>
                            </div>
                            <i class="fas fa-boxes text-4xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Pengajuan</p>
                                <p class="text-3xl font-bold"><?= $totalRequests ?></p>
                            </div>
                            <i class="fas fa-truck text-4xl text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Selesai</p>
                                <p class="text-3xl font-bold"><?= $completedRequests ?></p>
                            </div>
                            <i class="fas fa-check-circle text-4xl text-green-600"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold">Aktivitas Terbaru</h2>
                    </div>
                    <div class="p-6">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="pb-2">Tanggal</th>
                                    <th class="pb-2">Aktivitas</th>
                                    <th class="pb-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT pr.*, u.nama as user_name, i.nama_barang 
                                                    FROM pickup_requests pr 
                                                    JOIN users u ON pr.warga_id = u.id 
                                                    JOIN items i ON pr.item_id = i.id 
                                                    ORDER BY pr.created_at DESC LIMIT 5");
                                while($row = $stmt->fetch()):
                                ?>
                                <tr class="border-b">
                                    <td class="py-3"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td class="py-3">Pengajuan barang "<?= $row['nama_barang'] ?>" oleh <?= $row['user_name'] ?></td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 rounded text-xs 
                                            <?= $row['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($row['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>