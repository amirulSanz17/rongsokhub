<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('admin');

// Delete user
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['delete']]);
    header('Location: users.php?msg=deleted');
    exit();
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin RongsokHub</title>
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
                    <a href="dashboard.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    <a href="users.php" class="flex items-center space-x-3 p-3 bg-green-700 rounded-lg"><i class="fas fa-users"></i><span>Kelola User</span></a>
                    <a href="collectors.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-store"></i><span>Kelola Pengepul</span></a>
                    <a href="categories.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-tags"></i><span>Kelola Kategori</span></a>
                    <a href="requests.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-truck"></i><span>Kelola Pengajuan</span></a>
                    <a href="reports.php" class="flex items-center space-x-3 p-3 hover:bg-green-700 rounded-lg"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                    <a href="../logout.php" class="flex items-center space-x-3 p-3 hover:bg-red-600 rounded-lg mt-8"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800">Kelola User</h1>
                </div>
                
                <?php if(isset($_GET['msg'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">User berhasil dihapus!</div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left">
                                <th class="px-6 py-3">ID</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">No. HP</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Tanggal Daftar</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4"><?= $user['id'] ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($user['nama']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($user['phone']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs <?= $user['role'] == 'warga' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td class="px-6 py-4">
                                    <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
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