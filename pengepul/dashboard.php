<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('pengepul');

$collectorId = $_SESSION['user_id'];

$newRequests = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE collector_id = ? AND status = 'pending'");
$newRequests->execute([$collectorId]);
$newRequests = $newRequests->fetchColumn();

$acceptedRequests = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE collector_id = ? AND status = 'accepted'");
$acceptedRequests->execute([$collectorId]);
$acceptedRequests = $acceptedRequests->fetchColumn();

$pickupRequests = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE collector_id = ? AND status = 'pickup'");
$pickupRequests->execute([$collectorId]);
$pickupRequests = $pickupRequests->fetchColumn();

$totalTransactions = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE collector_id = ? AND status = 'completed'");
$totalTransactions->execute([$collectorId]);
$totalTransactions = $totalTransactions->fetchColumn();

// Get collector profile
$profile = $pdo->prepare("SELECT * FROM collector_profiles WHERE user_id = ?");
$profile->execute([$collectorId]);
$profile = $profile->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengepul - RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-orange-800 text-white">
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-recycle text-2xl"></i>
                    <span class="text-xl font-bold">RongsokHub</span>
                </div>
                <div class="mb-6 p-3 bg-orange-700 rounded-lg">
                    <p class="text-sm">Halo,</p>
                    <p class="font-bold"><?= $_SESSION['nama'] ?></p>
                    <p class="text-xs mt-1"><?= $profile['nama_usaha'] ?: 'Belum mengisi profil' ?></p>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 bg-orange-700 rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="marketplace.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg">
                        <i class="fas fa-store"></i>
                        <span>Marketplace Barang</span>
                    </a>
                    <a href="incoming.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg">
                        <i class="fas fa-inbox"></i>
                        <span>Pengajuan Masuk</span>
                        <?php if($newRequests > 0): ?>
                            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= $newRequests ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="pickup.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg">
                        <i class="fas fa-truck"></i>
                        <span>Barang Dijemput</span>
                    </a>
                    <a href="history.php" class="flex items-center space-x-3 p-3 hover:bg-orange-700 rounded-lg">
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
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Pengepul</h1>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pengajuan Baru</p>
                                <p class="text-3xl font-bold"><?= $newRequests ?></p>
                            </div>
                            <i class="fas fa-envelope text-4xl text-orange-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pengajuan Diterima</p>
                                <p class="text-3xl font-bold"><?= $acceptedRequests ?></p>
                            </div>
                            <i class="fas fa-check-circle text-4xl text-green-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Barang Dijemput</p>
                                <p class="text-3xl font-bold"><?= $pickupRequests ?></p>
                            </div>
                            <i class="fas fa-truck text-4xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Transaksi</p>
                                <p class="text-3xl font-bold"><?= $totalTransactions ?></p>
                            </div>
                            <i class="fas fa-chart-line text-4xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Completion Alert -->
                <?php if(!$profile['nama_usaha']): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <div>
                            <p class="font-bold">Profil belum lengkap!</p>
                            <p class="text-sm">Silakan lengkapi profil usaha Anda agar warga dapat melihat informasi pengepul.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Tips -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-tips text-4xl"></i>
                        <div>
                            <h3 class="text-xl font-bold">Tips untuk Pengepul</h3>
                            <p>Respon cepat pengajuan warga dan jadwal penjemputan yang tepat waktu akan meningkatkan reputasi Anda!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>