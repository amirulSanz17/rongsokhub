<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$userId = $_SESSION['user_id'];

$totalItems = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ?");
$totalItems->execute([$userId]);
$totalItems = $totalItems->fetchColumn();

$activeItems = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ? AND status = 'tersedia'");
$activeItems->execute([$userId]);
$activeItems = $activeItems->fetchColumn();

$completedItems = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests pr JOIN items i ON pr.item_id = i.id WHERE i.user_id = ? AND pr.status = 'completed'");
$completedItems->execute([$userId]);
$completedItems = $completedItems->fetchColumn();

$pendingRequests = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests pr JOIN items i ON pr.item_id = i.id WHERE i.user_id = ? AND pr.status = 'pending'");
$pendingRequests->execute([$userId]);
$pendingRequests = $pendingRequests->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Warga - RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white">
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
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 bg-blue-700 rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="items.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg">
                        <i class="fas fa-box"></i>
                        <span>Data Barang</span>
                    </a>
                    <a href="add_item.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg">
                        <i class="fas fa-plus-circle"></i>
                        <span>Tambah Barang</span>
                    </a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg">
                        <i class="fas fa-store"></i>
                        <span>Daftar Pengepul</span>
                    </a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg">
                        <i class="fas fa-truck"></i>
                        <span>Pengajuan Saya</span>
                    </a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-blue-700 rounded-lg">
                        <i class="fas fa-history"></i>
                        <span>Riwayat</span>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Warga</h1>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
                                <p class="text-gray-500 text-sm">Barang Aktif</p>
                                <p class="text-3xl font-bold"><?= $activeItems ?></p>
                            </div>
                            <i class="fas fa-check-circle text-4xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Barang Selesai</p>
                                <p class="text-3xl font-bold"><?= $completedItems ?></p>
                            </div>
                            <i class="fas fa-check-double text-4xl text-purple-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pengajuan Pending</p>
                                <p class="text-3xl font-bold"><?= $pendingRequests ?></p>
                            </div>
                            <i class="fas fa-clock text-4xl text-yellow-600"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Tips Card -->
                <div class="bg-gradient-to-r from-green-500 to-blue-500 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-lightbulb text-4xl"></i>
                        <div>
                            <h3 class="text-xl font-bold">Tips Menjual Barang Rongsok</h3>
                            <p>Pastikan foto barang jelas dan deskripsi lengkap agar pengepul tertarik mengambil barang Anda!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>