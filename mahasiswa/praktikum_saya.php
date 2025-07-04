<?php
$page_title = 'Praktikum Saya';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];

// Ambil daftar praktikum yang diikuti mahasiswa
$stmt = $pdo->prepare("
    SELECT mp.*, u.nama as nama_asisten, p.tanggal_daftar
    FROM mata_praktikum mp
    JOIN pendaftaran p ON mp.id = p.mata_praktikum_id
    JOIN users u ON mp.asisten_id = u.id
    WHERE p.mahasiswa_id = ? AND p.status = 'aktif'
    ORDER BY p.tanggal_daftar DESC
");
$stmt->execute([$mahasiswa_id]);
$praktikum_saya = $stmt->fetchAll();
?>

<div class="space-y-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Praktikum yang Diikuti</h2>
        <p class="text-gray-600 mb-6">Berikut adalah daftar mata praktikum yang sedang Anda ikuti.</p>
        <?php if (empty($praktikum_saya)): ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-500">Anda belum mengikuti praktikum apapun.</p>
                <a href="katalog.php" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Daftar Praktikum
                </a>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 gap-6">
                <?php foreach ($praktikum_saya as $praktikum): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    <?= htmlspecialchars($praktikum['kode']) ?>
                                </span>
                                <span class="text-sm text-gray-500"><?= $praktikum['sks'] ?> SKS</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-1"><?= htmlspecialchars($praktikum['nama']) ?></h3>
                            <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>
                            <div class="flex items-center text-sm text-gray-500 mb-1">
                                <i class="fas fa-user mr-1"></i>
                                <?= htmlspecialchars($praktikum['nama_asisten']) ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                Semester <?= $praktikum['semester'] ?>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="detail_praktikum.php?id=<?= $praktikum['id'] ?>" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition text-center block">
                                <i class="fas fa-eye mr-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
