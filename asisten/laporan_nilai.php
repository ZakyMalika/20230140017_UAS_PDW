<?php
require_once '../config.php';
requireAsisten();
$pageTitle = 'Nilai Laporan';
$activePage = 'laporan';
require_once 'templates/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$asisten_id = $_SESSION['user_id'];

// Ambil detail laporan
$stmt = $pdo->prepare("
    SELECT l.*, u.nama AS nama_mahasiswa, m.judul AS judul_modul, mp.nama AS nama_praktikum, mp.asisten_id
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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = $_POST['nilai'];
    $feedback = trim($_POST['feedback']);
    if ($nilai === '' || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $error = 'Nilai harus diisi (0-100).';
    } else {
        $stmt = $pdo->prepare("UPDATE laporan SET nilai=?, feedback=?, status='dinilai' WHERE id=?");
        if ($stmt->execute([$nilai, $feedback, $id])) {
            $success = 'Laporan berhasil dinilai.';
            // Refresh data
            $stmt = $pdo->prepare("
                SELECT l.*, u.nama AS nama_mahasiswa, m.judul AS judul_modul, mp.nama AS nama_praktikum, mp.asisten_id
                FROM laporan l
                JOIN users u ON l.mahasiswa_id = u.id
                JOIN modul m ON l.modul_id = m.id
                JOIN mata_praktikum mp ON m.mata_praktikum_id = mp.id
                WHERE l.id = ?
            ");
            $stmt->execute([$id]);
            $laporan = $stmt->fetch();
        } else {
            $error = 'Gagal menyimpan nilai.';
        }
    }
}
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold mb-4">Penilaian Laporan Praktikum</h2>
    <div class="mb-2"><b>Mahasiswa:</b> <?= htmlspecialchars($laporan['nama_mahasiswa']) ?></div>
    <div class="mb-2"><b>Praktikum:</b> <?= htmlspecialchars($laporan['nama_praktikum']) ?></div>
    <div class="mb-2"><b>Modul:</b> <?= htmlspecialchars($laporan['judul_modul']) ?></div>
    <div class="mb-2"><b>Tanggal Upload:</b> <?= formatTanggal($laporan['tanggal_upload']) ?></div>
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
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($laporan['status'] == 'dinilai'): ?>
        <div class="mb-2"><b>Nilai:</b> <?= htmlspecialchars($laporan['nilai']) ?></div>
        <div class="mb-2"><b>Feedback:</b> <?= nl2br(htmlspecialchars($laporan['feedback'])) ?></div>
        <div class="mt-4">
            <a href="laporan.php" class="text-blue-600 hover:underline"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Laporan</a>
        </div>
    <?php else: ?>
        <form method="post" class="space-y-4 mt-4">
            <div>
                <label class="block font-medium mb-1">Nilai (0-100)</label>
                <input type="number" name="nilai" min="0" max="100" required class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($laporan['nilai'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Feedback</label>
                <textarea name="feedback" class="w-full border rounded px-3 py-2" rows="3"><?= htmlspecialchars($laporan['feedback'] ?? '') ?></textarea>
            </div>
            <div>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"><i class="fas fa-check mr-1"></i>Simpan Nilai</button>
                <a href="laporan_detail.php?id=<?= $laporan['id'] ?>" class="ml-2 text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>
