<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('admin');

// Get statistics
$totalWarga = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'warga'")->fetchColumn();
$totalPengepul = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengepul'")->fetchColumn();
$totalItems = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$totalRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests")->fetchColumn();
$completedRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'completed'")->fetchColumn();

// Monthly transactions
$monthlyStats = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM pickup_requests 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
")->fetchAll();

// Top categories
$topCategories = $pdo->query("
    SELECT c.nama_kategori, COUNT(i.id) as total_items
    FROM items i
    JOIN categories c ON i.category_id = c.id
    GROUP BY c.id
    ORDER BY total_items DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-truck"></i><span>Kelola Pengajuan</span></a>
                    <a href="reports.php" class="flex items-center space-x-3 p-3 bg-green-700 rounded-lg"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <div class="flex-1 ml-64 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Laporan & Statistik</h1>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-gray-500 text-sm">Total Warga</p>
                        <p class="text-2xl font-bold text-blue-600"><?= $totalWarga ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-gray-500 text-sm">Total Pengepul</p>
                        <p class="text-2xl font-bold text-orange-600"><?= $totalPengepul ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-gray-500 text-sm">Total Barang</p>
                        <p class="text-2xl font-bold text-green-600"><?= $totalItems ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-gray-500 text-sm">Total Pengajuan</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $totalRequests ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-gray-500 text-sm">Selesai</p>
                        <p class="text-2xl font-bold text-green-600"><?= $completedRequests ?></p>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold mb-4">Trend Transaksi 6 Bulan Terakhir</h3>
                        <canvas id="transactionChart" height="250"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold mb-4">Kategori Barang Terlaris</h3>
                        <canvas id="categoryChart" height="250"></canvas>
                    </div>
                </div>
                
                <!-- Export Button -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Export Laporan</h3>
                    <div class="flex gap-4">
                        <button onclick="exportToPDF()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button onclick="exportToExcel()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Transaction Chart
        const ctx1 = document.getElementById('transactionChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column(array_reverse($monthlyStats), 'month')) ?>,
                datasets: [{
                    label: 'Total Pengajuan',
                    data: <?= json_encode(array_column(array_reverse($monthlyStats), 'total')) ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }, {
                    label: 'Selesai',
                    data: <?= json_encode(array_column(array_reverse($monthlyStats), 'completed')) ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    tension: 0.1
                }]
            }
        });
        
        // Category Chart
        const ctx2 = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($topCategories, 'nama_kategori')) ?>,
                datasets: [{
                    label: 'Jumlah Barang',
                    data: <?= json_encode(array_column($topCategories, 'total_items')) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)'
                }]
            }
        });
        
        function exportToPDF() {
            alert('Fitur export PDF akan segera tersedia');
        }
        
        function exportToExcel() {
            alert('Fitur export Excel akan segera tersedia');
        }
    </script>
</body>
</html>