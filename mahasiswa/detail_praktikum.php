<?php
require_once '../config.php';
requireLogin();

if (!isMahasiswa()) {
    redirectTo('../asisten/dashboard.php');
}

$page_title = 'Detail Praktikum';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil info praktikum
$stmt = $pdo->prepare("
    SELECT mp.*, u.nama as nama_asisten, u.email as email_asisten
    FROM mata_praktikum mp
    JOIN users u ON mp.asisten_id = u.id
    WHERE mp.id = ?
");
$stmt->execute([$id]);
$praktikum = $stmt->fetch();

if (!$praktikum) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Praktikum tidak ditemukan.</div>';
    require_once 'templates/footer_mahasiswa.php';
    exit;
}

// Cek status pendaftaran mahasiswa
$stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE mahasiswa_id = ? AND mata_praktikum_id = ? AND status = 'aktif'");
$stmt->execute([$mahasiswa_id, $id]);
$pendaftaran = $stmt->fetch();

// Ambil daftar modul
$stmt = $pdo->prepare("SELECT * FROM modul WHERE mata_praktikum_id = ? ORDER BY pertemuan_ke ASC");
$stmt->execute([$id]);
$modul = $stmt->fetchAll();

// Proses upload laporan
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_laporan']) && $pendaftaran) {
    $modul_id = (int)$_POST['modul_id'];
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['pdf', 'doc', 'docx', 'zip'];
        $ext = strtolower(pathinfo($_FILES['file_laporan']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $upload_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Format file tidak didukung.</div>';
        } else {
            $uploadDir = '../uploads/laporan/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . '_' . $mahasiswa_id . '_' . $modul_id . '.' . $ext;
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target)) {
                // Cek jika sudah pernah upload
                $stmt = $pdo->prepare("SELECT id FROM laporan WHERE mahasiswa_id=? AND modul_id=?");
                $stmt->execute([$mahasiswa_id, $modul_id]);
                if ($stmt->fetch()) {
                    $upload_message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">Anda sudah mengumpulkan laporan untuk modul ini.</div>';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan) VALUES (?, ?, ?)");
                    if ($stmt->execute([$mahasiswa_id, $modul_id, $filename])) {
                        $upload_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Laporan berhasil diupload.</div>';
                    } else {
                        $upload_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Gagal upload laporan.</div>';
                        @unlink($target);
                    }
                }
            } else {
                $upload_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Gagal upload file.</div>';
            }
        }
    } else {
        $upload_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Pilih file laporan terlebih dahulu.</div>';
    }
}

// Ambil daftar laporan yang sudah dikumpulkan mahasiswa untuk praktikum ini
$laporan_mahasiswa = [];
if ($pendaftaran) {
    $stmt = $pdo->prepare("SELECT * FROM laporan WHERE mahasiswa_id = ? AND modul_id IN (SELECT id FROM modul WHERE mata_praktikum_id = ?)");
    $stmt->execute([$mahasiswa_id, $id]);
    while ($row = $stmt->fetch()) {
        $laporan_mahasiswa[$row['modul_id']] = $row;
    }
}
?>

<div class="max-w-3xl mx-auto space-y-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($praktikum['nama']) ?></h2>
        <p class="text-gray-700 mb-2"><?= htmlspecialchars($praktikum['deskripsi']) ?></p>
        <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-2">
            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">Kode: <?= htmlspecialchars($praktikum['kode']) ?></span>
            <span class="bg-green-100 text-green-800 px-2 py-1 rounded"><?= $praktikum['sks'] ?> SKS</span>
            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">Semester <?= $praktikum['semester'] ?></span>
        </div>
        <div class="mt-2">
            <span class="text-gray-700">Asisten: </span>
            <span class="font-semibold"><?= htmlspecialchars($praktikum['nama_asisten']) ?></span>
            <span class="text-gray-500 text-xs">(<?= htmlspecialchars($praktikum['email_asisten']) ?>)</span>
        </div>
        <div class="mt-4">
            <?php if ($pendaftaran): ?>
                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded">Anda sudah terdaftar</span>
            <?php else: ?>
                <form method="post" action="katalog.php" class="inline">
                    <input type="hidden" name="mata_praktikum_id" value="<?= $praktikum['id'] ?>">
                    <button type="submit" name="daftar" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        <i class="fas fa-check mr-2"></i>Daftar Praktikum Ini
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">Daftar Modul</h3>
        <?= $upload_message ?>
        <?php if (empty($modul)): ?>
            <div class="text-gray-500">Belum ada modul pada praktikum ini.</div>
        <?php else: ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($modul as $m): ?>
                    <li class="py-3">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div>
                                <span class="font-semibold">Pertemuan <?= $m['pertemuan_ke'] ?>:</span>
                                <span><?= htmlspecialchars($m['judul']) ?></span>
                                <?php if (!empty($m['file_materi'])): ?>
                                    <a href="../uploads/materi/<?= htmlspecialchars($m['file_materi']) ?>" class="text-blue-600 hover:underline ml-2" target="_blank">
                                        <i class="fas fa-download"></i> Materi
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($m['deskripsi'])): ?>
                                    <div class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($m['deskripsi']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 md:mt-0">
                                <?php if ($pendaftaran): ?>
                                    <?php if (isset($laporan_mahasiswa[$m['id']])): ?>
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded">
                                            Sudah dikumpulkan
                                            <?php if ($laporan_mahasiswa[$m['id']]['status'] == 'dinilai'): ?>
                                                | Nilai: <b><?= $laporan_mahasiswa[$m['id']]['nilai'] ?></b>
                                            <?php endif; ?>
                                            <a href="../uploads/laporan/<?= htmlspecialchars($laporan_mahasiswa[$m['id']]['file_laporan']) ?>" class="text-blue-600 hover:underline ml-2" target="_blank">
                                                <i class="fas fa-file-download"></i> Lihat
                                            </a>
                                        </span>
                                    <?php else: ?>
                                        <!-- Form upload laporan -->
                                        <form method="post" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-2 items-center">
                                            <input type="hidden" name="modul_id" value="<?= $m['id'] ?>">
                                            <input type="file" name="file_laporan" required class="border rounded px-2 py-1 text-sm">
                                            <button type="submit" name="upload_laporan" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                                                <i class="fas fa-upload mr-1"></i>Upload Laporan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
