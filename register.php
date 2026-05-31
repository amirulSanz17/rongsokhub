<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $phone, $role]);
        
        if ($role === 'pengepul') {
            $userId = $pdo->lastInsertId();
            $stmt2 = $pdo->prepare("INSERT INTO collector_profiles (user_id, nama_usaha, alamat, area_operasional, deskripsi) VALUES (?, ?, ?, ?, ?)");
            $stmt2->execute([$userId, '', '', '', '']);
        }
        
        $success = 'Pendaftaran berhasil! Silakan login.';
    } catch(PDOException $e) {
        $error = 'Email sudah terdaftar!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RongsokHub - Daftar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-8">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-6">
                <i class="fas fa-recycle text-4xl text-green-600"></i>
                <h2 class="text-2xl font-bold">Daftar Akun</h2>
            </div>
            
            <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Nomor HP</label>
                    <input type="text" name="phone" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Daftar Sebagai</label>
                    <select name="role" required class="w-full px-4 py-2 border rounded-lg">
                        <option value="warga">Warga</option>
                        <option value="pengepul">Pengepul</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">Daftar</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Sudah punya akun? <a href="index.php" class="text-green-600 hover:underline">Login</a></p>
        </div>
    </div>
</body>
</html>