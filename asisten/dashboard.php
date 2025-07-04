<?php
// 1. Definisi Variabel untuk Template
require_once '../config.php'; // pastikan koneksi database
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Panggil Header
require_once 'templates/header.php'; 

$asisten_id = $_SESSION['user_id'];

// Ambil 5 laporan terbaru yang belum dinilai
$stmt = $pdo->prepare("
    SELECT l.*, u.nama AS nama_mahasiswa, m.judul AS judul_modul, mp.nama AS nama_praktikum
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
    WHERE mp.asisten_id = ? AND l.status = 'dikumpulkan'
    ORDER BY l.tanggal_upload DESC
    LIMIT 5
");
$stmt->execute([$asisten_id]);
$laporan_belum_dinilai = $stmt->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800">12</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800">152</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800">18</p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Laporan Belum Dinilai</h3>
    <?php if (empty($laporan_belum_dinilai)): ?>
        <div class="text-center py-8">
            <i class="fas fa-check-circle text-4xl text-green-300 mb-2"></i>
            <p class="text-gray-500">Tidak ada laporan yang perlu dinilai.</p>
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($laporan_belum_dinilai as $l): ?>
                        <tr>
                            <td class="px-4 py-2"><?= htmlspecialchars($l['nama_mahasiswa']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($l['nama_praktikum']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($l['judul_modul']) ?></td>
                            <td class="px-4 py-2"><?= formatTanggal($l['tanggal_upload']) ?></td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="laporan_detail.php?id=<?= $l['id'] ?>" class="text-blue-600 hover:underline"><i class="fas fa-eye"></i> Detail</a>
                                <a href="laporan_nilai.php?id=<?= $l['id'] ?>" class="text-green-600 hover:underline"><i class="fas fa-check"></i> Nilai</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// 3. Panggil Footer
require_once 'templates/footer.php';
?>