<?php
require_once '../config.php'; // pastikan path ini sesuai struktur folder Anda
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';

$asisten_id = $_SESSION['user_id'];

// Ambil laporan masuk untuk praktikum yang diasisteni
$stmt = $pdo->prepare("
    SELECT l.*, u.nama AS nama_mahasiswa, m.judul AS judul_modul, mp.nama AS nama_praktikum
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
    WHERE mp.asisten_id = ?
    ORDER BY l.tanggal_upload DESC
");
$stmt->execute([$asisten_id]);
$laporan = $stmt->fetchAll();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Laporan Masuk</h2>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <?php if (empty($laporan)): ?>
        <div class="text-center py-8">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Belum ada laporan yang masuk.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Praktikum</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Modul</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Upload</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($laporan as $l): ?>
                        <tr>
                            <td class="px-4 py-2"><?= htmlspecialchars($l['nama_mahasiswa']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($l['nama_praktikum']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($l['judul_modul']) ?></td>
                            <td class="px-4 py-2"><?= formatTanggal($l['tanggal_upload']) ?></td>
                            <td class="px-4 py-2">
                                <?php if ($l['status'] == 'dinilai'): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Dinilai</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Belum Dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="laporan_detail.php?id=<?= $l['id'] ?>" class="text-blue-600 hover:underline"><i class="fas fa-eye"></i> Detail</a>
                                <?php if ($l['status'] != 'dinilai'): ?>
                                    <a href="laporan_nilai.php?id=<?= $l['id'] ?>" class="text-green-600 hover:underline"><i class="fas fa-check"></i> Nilai</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
