<?php
require_once '../config.php'; // pastikan koneksi database tersedia
$page_title = 'Katalog Praktikum';
require_once 'templates/header_mahasiswa.php';

$mahasiswa_id = $_SESSION['user_id'];
$message = '';
 
// Handle pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    $mata_praktikum_id = (int)$_POST['mata_praktikum_id'];
    
    // Check apakah sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM pendaftaran WHERE mahasiswa_id = ? AND mata_praktikum_id = ?");
    $stmt->execute([$mahasiswa_id, $mata_praktikum_id]);
    
    if ($stmt->fetch()) {
        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 alert-auto-hide">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      Anda sudah terdaftar di mata praktikum ini
                    </div>';
    } else {
        // Daftar ke praktikum
        $stmt = $pdo->prepare("INSERT INTO pendaftaran (mahasiswa_id, mata_praktikum_id) VALUES (?, ?)");
        if ($stmt->execute([$mahasiswa_id, $mata_praktikum_id])) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 alert-auto-hide">
                          <i class="fas fa-check-circle mr-2"></i>
                          Berhasil mendaftar ke mata praktikum
                        </div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 alert-auto-hide">
                          <i class="fas fa-exclamation-triangle mr-2"></i>
                          Gagal mendaftar ke mata praktikum
                        </div>';
        }
    }
}

// Ambil daftar mata praktikum dengan status pendaftaran
$stmt = $pdo->prepare("
    SELECT mp.*, u.nama as nama_asisten,
           p.id as pendaftaran_id
    FROM mata_praktikum mp
    JOIN users u ON mp.asisten_id = u.id
    LEFT JOIN pendaftaran p ON p.mata_praktikum_id = mp.id AND p.mahasiswa_id = ?
    ORDER BY mp.nama
");
$stmt->execute([$mahasiswa_id]);
$mata_praktikum = $stmt->fetchAll();

// Hitung jumlah modul per mata praktikum
$stmt = $pdo->prepare("
    SELECT mata_praktikum_id, COUNT(*) as jumlah_modul
    FROM modul
    GROUP BY mata_praktikum_id
");
$stmt->execute();
$jumlah_modul = [];
while ($row = $stmt->fetch()) {
    $jumlah_modul[$row['mata_praktikum_id']] = $row['jumlah_modul'];
}

// Hitung jumlah mahasiswa yang terdaftar per mata praktikum
$stmt = $pdo->prepare("
    SELECT mata_praktikum_id, COUNT(*) as jumlah_mahasiswa
    FROM pendaftaran
    WHERE status = 'aktif'
    GROUP BY mata_praktikum_id
");
$stmt->execute();
$jumlah_mahasiswa = [];
while ($row = $stmt->fetch()) {
    $jumlah_mahasiswa[$row['mata_praktikum_id']] = $row['jumlah_mahasiswa'];
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Katalog Mata Praktikum</h2>
            <p class="text-gray-600 mt-1">Temukan dan daftar mata praktikum yang sesuai minat dan jadwal Anda.</p>
        </div>
    </div>

    <!-- Pesan -->
    <?php if ($message): ?>
        <div class="alert-container">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Daftar Mata Praktikum -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($mata_praktikum as $mp): ?>
                <div class="bg-gray-50 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($mp['nama']) ?></h3>
                    <p class="text-gray-600"><?= htmlspecialchars($mp['deskripsi']) ?></p>

                    <!-- Tampilkan jumlah modul jika ada -->
                    <?php if (isset($jumlah_modul[$mp['id']])): ?>
                        <p class="text-gray-500 text-sm mt-1">
                            Jumlah Modul: <?= $jumlah_modul[$mp['id']] ?>
                        </p>
                    <?php endif; ?>

                    <!-- Tampilkan jumlah mahasiswa yang terdaftar jika ada -->
                    <?php if (isset($jumlah_mahasiswa[$mp['id']])): ?>
                        <p class="text-gray-500 text-sm">
                            Terdaftar: <?= $jumlah_mahasiswa[$mp['id']] ?> mahasiswa
                        </p>
                    <?php endif; ?>

                    <div class="mt-4">
                        <?php if (isset($mp['pendaftaran_id'])): ?>
                            <!-- Sudah terdaftar -->
                            <form action="batal_daftar.php" method="post" class="inline">
                                <input type="hidden" name="pendaftaran_id" value="<?= $mp['pendaftaran_id'] ?>">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg shadow hover:bg-red-700 transition-all">
                                    <i class="fas fa-times mr-2"></i> Batalkan Pendaftaran
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Belum terdaftar -->
                            <form action="" method="post" class="inline">
                                <input type="hidden" name="mata_praktikum_id" value="<?= $mp['id'] ?>">
                                <button type="submit" name="daftar" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition-all">
                                    <i class="fas fa-check mr-2"></i> Daftar Praktikum
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>