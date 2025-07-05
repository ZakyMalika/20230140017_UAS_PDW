<?php
require_once '../config.php';
requireAsisten();
$pageTitle = 'Detail Laporan';
$activePage = 'laporan';
require_once 'templates/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$asisten_id = $_SESSION['user_id'];

// Ambil detail laporan
$stmt = $pdo->prepare("
    SELECT l.*, u.nama AS nama_mahasiswa, u.email AS email_mahasiswa, m.judul AS judul_modul, mp.nama AS nama_praktikum, mp.asisten_id
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul m ON l.modul_id = m.id
    JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
    WHERE l.id = ?
");
$stmt->execute([$id]);
$laporan = $stmt->fetch();

if (!$laporan || $laporan['asisten_id'] != $asisten_id) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Laporan tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>';
    require_once 'templates/footer.php';
    exit;
}
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold mb-4">Detail Laporan Praktikum</h2>
    <div class="mb-2"><b>Mahasiswa:</b> <?= htmlspecialchars($laporan['nama_mahasiswa']) ?> (<?= htmlspecialchars($laporan['email_mahasiswa']) ?>)</div>
    <div class="mb-2"><b>Praktikum:</b> <?= htmlspecialchars($laporan['nama_praktikum']) ?></div>
    <div class="mb-2"><b>Modul:</b> <?= htmlspecialchars($laporan['judul_modul']) ?></div>
    <div class="mb-2"><b>Tanggal Upload:</b> <?= formatTanggal($laporan['tanggal_upload']) ?></div>
    <div class="mb-2"><b>Status:</b>
        <?php if ($laporan['status'] == 'dinilai'): ?>
            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Dinilai</span>
        <?php else: ?>
            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Belum Dinilai</span>
        <?php endif; ?>
    </div>
    <div class="mb-2">
        <b>File Laporan:</b>
        <?php if (!empty($laporan['file_laporan'])): ?>
            <a href="../uploads/laporan/<?= htmlspecialchars($laporan['file_laporan']) ?>" target="_blank" class="text-blue-600 hover:underline">
                <i class="fas fa-download"></i> Download
            </a>
        <?php else: ?>
            <span class="text-gray-400">-</span>
        <?php endif; ?>
    </div>
    <?php if ($laporan['status'] == 'dinilai'): ?>
        <div class="mb-2"><b>Nilai:</b> <?= htmlspecialchars($laporan['nilai']) ?></div>
        <div class="mb-2"><b>Feedback:</b> <?= nl2br(htmlspecialchars($laporan['feedback'])) ?></div>
    <?php endif; ?>
    <div class="mt-4">
        <a href="laporan.php" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Laporan</a>
        <?php if ($laporan['status'] != 'dinilai'): ?>
            <a href="laporan_nilai.php?id=<?= $laporan['id'] ?>" class="ml-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"><i class="fas fa-check"></i> Nilai Laporan</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
