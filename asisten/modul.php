<?php
require_once '../config.php';
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
require_once 'templates/header.php';

$asisten_id = $_SESSION['user_id'];

// Ambil daftar modul yang diajarkan asisten
$stmt = $pdo->prepare("
    SELECT m.*, mp.nama AS nama_praktikum, mp.kode
    FROM modul m
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
    WHERE mp.asisten_id = ?
    ORDER BY mp.nama, m.pertemuan_ke
");
$stmt->execute([$asisten_id]);
$modul = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Daftar Modul</h2>
    <a href="modul_tambah.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>Tambah Modul
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <?php if (empty($modul)): ?>
        <div class="text-center py-8">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Belum ada modul yang dibuat.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Praktikum</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Judul Modul</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pertemuan</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($modul as $m): ?>
                        <tr>
                            <td class="px-4 py-2"><?= htmlspecialchars($m['kode']) ?> - <?= htmlspecialchars($m['nama_praktikum']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($m['judul']) ?></td>
                            <td class="px-4 py-2">Pertemuan <?= $m['pertemuan_ke'] ?></td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="modul_edit.php?id=<?= $m['id'] ?>" class="text-blue-600 hover:underline"><i class="fas fa-edit"></i> Edit</a>
                                <a href="modul_hapus.php?id=<?= $m['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Hapus modul ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
