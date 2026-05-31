<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'warga') {
        header('Location: warga/dashboard.php');
    } elseif ($_SESSION['role'] === 'pengepul') {
        header('Location: pengepul/dashboard.php');
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($user['role'] === 'warga') {
            header('Location: warga/dashboard.php');
        } else {
            header('Location: pengepul/dashboard.php');
        }
        exit();
    } else {
        $error = 'Email atau password salah!';
    }
}

// Get statistics for homepage
$totalWarga = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'warga'")->fetchColumn();
$totalPengepul = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengepul'")->fetchColumn();
$totalItems = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'tersedia'")->fetchColumn();
$totalCompleted = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'completed'")->fetchColumn();

// Get latest items
$latestItems = $pdo->query("
    SELECT i.*, c.nama_kategori, 
           (SELECT foto FROM item_photos WHERE item_id = i.id LIMIT 1) as foto 
    FROM items i 
    JOIN categories c ON i.category_id = c.id 
    WHERE i.status = 'tersedia' 
    ORDER BY i.created_at DESC 
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RongsokHub - Platform Jual Beli Barang Rongsok</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-recycle text-3xl text-green-600"></i>
                    <span class="text-2xl font-bold text-gray-800">Rongsok<span class="text-green-600">Hub</span></span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-green-600 transition">Beranda</a>
                    <a href="#features" class="text-gray-700 hover:text-green-600 transition">Fitur</a>
                    <a href="#marketplace" class="text-gray-700 hover:text-green-600 transition">Marketplace</a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-green-600 transition">Cara Kerja</a>
                    <a href="#statistics" class="text-gray-700 hover:text-green-600 transition">Statistik</a>
                </div>
                <a href="#login" onclick="showLoginModal()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient text-white py-20">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">
                        Ubah Sampah Jadi <span class="text-yellow-300">Berkah</span>
                    </h1>
                    <p class="text-xl mb-6 opacity-90">
                        Platform digital yang menghubungkan warga dengan pengepul barang rongsok. 
                        Mudah, cepat, dan menguntungkan!
                    </p>
                    <div class="flex space-x-4">
                        <a href="#register" onclick="showRegisterModal()" class="bg-white text-green-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                            <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                        </a>
                        <a href="#marketplace" class="border-2 border-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-700 transition">
                            <i class="fas fa-store mr-2"></i>Lihat Barang
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <img src="assets\uploads\image copy.png" alt="Recycle" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
                <i class="fas fa-star text-green-600 mr-2"></i>
                Fitur Unggulan RongsokHub
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="feature-card bg-gray-50 rounded-lg p-6 text-center shadow-md">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Mudah Jual Rongsok</h3>
                    <p class="text-gray-600">Upload foto barang rongsok Anda, tentukan harga, dan tunggu pengepul menghubungi</p>
                </div>
                <div class="feature-card bg-gray-50 rounded-lg p-6 text-center shadow-md">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-truck text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Penjemputan Langsung</h3>
                    <p class="text-gray-600">Pengepul akan menjemput barang rongsok Anda langsung ke lokasi</p>
                </div>
                <div class="feature-card bg-gray-50 rounded-lg p-6 text-center shadow-md">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Pantau Transaksi</h3>
                    <p class="text-gray-600">Lihat riwayat penjualan dan status penjemputan secara real-time</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Marketplace Section -->
    <section id="marketplace" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-4">
                <i class="fas fa-store text-green-600 mr-2"></i>
                Barang Rongsok Tersedia
            </h2>
            <p class="text-center text-gray-600 mb-12">Temukan berbagai barang rongsok yang siap dijemput</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($latestItems as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <?php if($item['foto'] && file_exists('assets/uploads/' . $item['foto'])): ?>
                            <img src="assets/uploads/<?= $item['foto'] ?>" alt="<?= htmlspecialchars($item['nama_barang']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-recycle text-6xl text-gray-400"></i>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($item['nama_barang']) ?></h3>
                        <p class="text-sm text-gray-600 mb-2">
                            <i class="fas fa-tag mr-1"></i><?= $item['nama_kategori'] ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-2">
                            <i class="fas fa-weight-hanging mr-1"></i><?= number_format($item['berat'], 0) ?> kg
                        </p>
                        <p class="text-sm text-gray-600 mb-3">
                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars(substr($item['alamat'], 0, 50)) ?>...
                        </p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">
                                Tersedia
                            </span>
                            <button onclick="showLoginModal()" class="text-green-600 hover:text-green-700 text-sm font-semibold">
                                Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(count($latestItems) == 0): ?>
            <div class="text-center py-12">
                <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Belum ada barang rongsok yang tersedia</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
                <i class="fas fa-question-circle text-green-600 mr-2"></i>
                Bagaimana Cara Kerjanya?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="text-xl font-bold mb-2">Daftar Akun</h3>
                    <p class="text-gray-600">Registrasi sebagai Warga atau Pengepul</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="text-xl font-bold mb-2">Upload/Temukan Barang</h3>
                    <p class="text-gray-600">Warga upload barang, Pengepul cari barang</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="text-xl font-bold mb-2">Penjemputan & Selesai</h3>
                    <p class="text-gray-600">Pengepul jemput barang, transaksi selesai</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="statistics" class="py-20 bg-green-600 text-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-12">Dampak Positif RongsokHub</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2"><?= number_format($totalWarga, 0) ?></div>
                    <p class="text-sm opacity-90">Warga Bergabung</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2"><?= number_format($totalPengepul, 0) ?></div>
                    <p class="text-sm opacity-90">Pengepul Aktif</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2"><?= number_format($totalItems, 0) ?></div>
                    <p class="text-sm opacity-90">Barang Terjual</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2"><?= number_format($totalCompleted, 0) ?></div>
                    <p class="text-sm opacity-90">Transaksi Selesai</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
                <i class="fas fa-comments text-green-600 mr-2"></i>
                Apa Kata Mereka?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">Budi Santoso</h4>
                            <p class="text-sm text-gray-600">Warga</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"Sangat membantu! Saya bisa jual barang bekas tanpa repot cari pengepul."</p>
                    <div class="mt-3 text-yellow-500">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-store text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">PT Berkah Rongsok</h4>
                            <p class="text-sm text-gray-600">Pengepul</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"Platform ini memudahkan kami mendapatkan supply barang rongsok secara konsisten."</p>
                    <div class="mt-3 text-yellow-500">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">Siti Aminah</h4>
                            <p class="text-sm text-gray-600">Warga</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"Rumah jadi lebih bersih, dapat uang tambahan juga. Konsepnya keren!"</p>
                    <div class="mt-3 text-yellow-500">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-recycle text-2xl text-green-500"></i>
                        <span class="text-xl font-bold">RongsokHub</span>
                    </div>
                    <p class="text-gray-400 text-sm">Mengubah sampah menjadi berkah untuk lingkungan yang lebih baik.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Tentang Kami</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-green-500">Tentang RongsokHub</a></li>
                        <li><a href="#" class="hover:text-green-500">Karir</a></li>
                        <li><a href="#" class="hover:text-green-500">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Bantuan</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-green-500">FAQ</a></li>
                        <li><a href="#" class="hover:text-green-500">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-green-500">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><i class="fas fa-envelope mr-2"></i> info@rongsokhub.com</li>
                        <li><i class="fas fa-phone mr-2"></i> 0812-3456-7890</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; 2024 RongsokHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Login RongsokHub</h3>
                <button onclick="closeLoginModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>
            <p class="text-center text-gray-600 mt-4">
                Belum punya akun? 
                <a href="register.php" class="text-green-600 hover:underline">Daftar Sekarang</a>
            </p>
        </div>
    </div>

    <script>
        function showLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
            document.getElementById('loginModal').classList.add('flex');
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.getElementById('loginModal').classList.remove('flex');
        }
        
        function showRegisterModal() {
            window.location.href = 'register.php';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            let modal = document.getElementById('loginModal');
            if (event.target == modal) {
                closeLoginModal();
            }
        }
    </script>
</body>
</html>