<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotRole('warga');

$userId = $_SESSION['user_id'];
$collectorId = $_GET['collector_id'];

// Get user's available items
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? AND status = 'tersedia' ORDER BY created_at DESC");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['item_id'];
    
    // Check if request already exists
    $check = $pdo->prepare("SELECT * FROM pickup_requests WHERE item_id = ? AND collector_id = ? AND status != 'rejected'");
    $check->execute([$itemId, $collectorId]);
    
    if ($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO pickup_requests (item_id, warga_id, collector_id, request_by, status) VALUES (?, ?, ?, 'warga', 'pending')");
        $stmt->execute([$itemId, $userId, $collectorId]);
        
        // Update item status
        $stmt2 = $pdo->prepare("UPDATE items SET status = 'menunggu_konfirmasi' WHERE id = ?");
        $stmt2->execute([$itemId]);
        
        header('Location: requests.php?msg=submitted');
        exit();
    } else {
        $error = "Anda sudah pernah mengajukan barang ini ke pengepul tersebut!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Barang - Warga RongsokHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-8">
        <div class="bg-white rounded-lg shadow p-8 max-w-2xl w-full">
            <h2 class="text-2xl font-bold mb-4">Pilih Barang untuk Dijemput</h2>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="space-y-4">
                    <?php foreach($items as $item): ?>
                    <label class="flex items-start p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="item_id" value="<?= $item['id'] ?>" required class="mt-1 mr-3">
                        <div class="flex-1">
                            <h3 class="font-bold"><?= htmlspecialchars($item['nama_barang']) ?></h3>
                            <p class="text-sm text-gray-600">Berat: <?= $item['berat'] ?> kg</p>
                            <p class="text-sm text-gray-600">Lokasi: <?= htmlspecialchars($item['alamat']) ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                    
                    <?php if(count($items) == 0): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-box-open text-6xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Anda belum memiliki barang yang tersedia</p>
                        <a href="add_item.php" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg">Tambah Barang</a>
                    </div>
                    <?php else: ?>
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Ajukan Penjemputan</button>
                        <a href="collectors.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">Batal</a>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>