<?php
$page_title = 'Dashboard';
require_once 'templates/header_mahasiswa.php';

// Ambil statistik
$mahasiswa_id = $_SESSION['user_id'];

// Hitung jumlah praktikum yang diikuti
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE mahasiswa_id = ? AND status = 'aktif'");
$stmt->execute([$mahasiswa_id]);
$total_praktikum = $stmt->fetchColumn();

// Hitung jumlah laporan yang sudah dikumpulkan
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM laporan l 
    JOIN modul m ON l.modul_id = m.id 
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id 
    JOIN pendaftaran p ON p.mata_praktikum_id = mp.id 
    WHERE l.mahasiswa_id = ? AND p.mahasiswa_id = ? AND p.status = 'aktif'
");
$stmt->execute([$mahasiswa_id, $mahasiswa_id]);
$total_laporan = $stmt->fetchColumn();

// Hitung jumlah laporan yang sudah dinilai
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM laporan l 
    JOIN modul m ON l.modul_id = m.id 
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id 
    JOIN pendaftaran p ON p.mata_praktikum_id = mp.id 
    WHERE l.mahasiswa_id = ? AND p.mahasiswa_id = ? AND p.status = 'aktif' AND l.status = 'dinilai'
");
$stmt->execute([$mahasiswa_id, $mahasiswa_id]);
$total_dinilai = $stmt->fetchColumn();

// Ambil praktikum terbaru yang diikuti
$stmt = $pdo->prepare("
    SELECT mp.*, u.nama as nama_asisten, p.tanggal_daftar
    FROM mata_praktikum mp
    JOIN pendaftaran p ON mp.id = p.mata_praktikum_id
    JOIN users u ON mp.asisten_id = u.id
    WHERE p.mahasiswa_id = ? AND p.status = 'aktif'
    ORDER BY p.tanggal_daftar DESC
    LIMIT 5
");
$stmt->execute([$mahasiswa_id]);
$praktikum_terbaru = $stmt->fetchAll();

// Ambil tugas terbaru yang belum dikumpulkan
$stmt = $pdo->prepare("
    SELECT m.*, mp.nama as nama_praktikum, mp.kode
    FROM modul m
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
    JOIN pendaftaran p ON p.mata_praktikum_id = mp.id
    LEFT JOIN laporan l ON l.modul_id = m.id AND l.mahasiswa_id = ?
    WHERE p.mahasiswa_id = ? AND p.status = 'aktif' AND l.id IS NULL
    ORDER BY m.created_at DESC
    LIMIT 5
");
$stmt->execute([$mahasiswa_id, $mahasiswa_id]);
$tugas_belum_selesai = $stmt->fetchAll();
?>

<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-2">Selamat Datang, <?= htmlspecialchars($current_user['nama']) ?>!</h2>
        <p class="text-blue-100">Semangat belajar dan kerjakan tugas praktikum dengan baik.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Praktikum Diikuti</p>
                    <p class="text-2xl font-bold text-blue-600"><?= $total_praktikum ?></p>
                </div>
                <i class="fas fa-book text-3xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Laporan Dikumpulkan</p>
                    <p class="text-2xl font-bold text-green-600"><?= $total_laporan ?></p>
                </div>
                <i class="fas fa-file-upload text-3xl text-green-600"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Laporan Dinilai</p>
                    <p class="text-2xl font-bold text-purple-600"><?= $total_dinilai ?></p>
                </div>
                <i class="fas fa-star text-3xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="katalog.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <i class="fas fa-search text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">Cari Praktikum</p>
                    <p class="text-sm text-gray-600">Temukan mata praktikum baru</p>
                </div>
            </a>

            <a href="praktikum_saya.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <i class="fas fa-book text-green-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">Praktikum Saya</p>
                    <p class="text-sm text-gray-600">Lihat praktikum yang diikuti</p>
                </div>
            </a>

            <a href="praktikum_saya.php" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                <i class="fas fa-file-upload text-purple-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-gray-900">Upload Laporan</p>
                    <p class="text-sm text-gray-600">Kumpulkan tugas praktikum</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Praktikum Terbaru -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Praktikum Terbaru</h3>
            
            <?php if (empty($praktikum_terbaru)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-book text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Belum ada praktikum yang diikuti</p>
                    <a href="katalog.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                        Cari Praktikum
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($praktikum_terbaru as $praktikum): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($praktikum['nama']) ?></h4>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($praktikum['nama_asisten']) ?></p>
                                <p class="text-xs text-gray-500">Bergabung: <?= formatTanggal($praktikum['tanggal_daftar']) ?></p>
                            </div>
                            <a href="detail_praktikum.php?id=<?= $praktikum['id'] ?>" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="praktikum_saya.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        Lihat Semua Praktikum
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tugas Belum Selesai -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Tugas Belum Selesai</h3>
            
            <?php if (empty($tugas_belum_selesai)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-2"></i>
                    <p class="text-gray-500">Semua tugas sudah selesai!</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($tugas_belum_selesai as $tugas): ?>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($tugas['judul']) ?></h4>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($tugas['nama_praktikum']) ?></p>
                                <p class="text-xs text-gray-500">Pertemuan ke-<?= $tugas['pertemuan_ke'] ?></p>
                            </div>
                            <span class="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs rounded-full">
                                Belum Selesai
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="praktikum_saya.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        Lihat Semua Tugas
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>