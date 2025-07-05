<?php
require_once '../config.php';
$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
require_once 'templates/header.php';

$asisten_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Ambil daftar praktikum yang diasisteni
$stmt = $pdo->prepare("SELECT id, nama FROM mata_praktikum WHERE asisten_id = ?");
$stmt->execute([$asisten_id]);
$praktikum_list = $stmt->fetchAll();

// Handle Tambah Modul
if (isset($_POST['tambah_modul'])) {
    $mata_praktikum_id = $_POST['mata_praktikum_id'];
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $pertemuan_ke = (int)$_POST['pertemuan_ke'];

    if (!$mata_praktikum_id || !$judul || !$pertemuan_ke) {
        $error = 'Semua field wajib diisi.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO modul (mata_praktikum_id, judul, deskripsi, pertemuan_ke) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$mata_praktikum_id, $judul, $deskripsi, $pertemuan_ke])) {
            $success = 'Modul berhasil ditambahkan.';
        } else {
            $error = 'Gagal menambah modul.';
        }
    }
}

// Handle Edit Modul
if (isset($_POST['edit_modul'])) {
    $id = $_POST['id'];
    $mata_praktikum_id = $_POST['mata_praktikum_id'];
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $pertemuan_ke = (int)$_POST['pertemuan_ke'];

    if (!$mata_praktikum_id || !$judul || !$pertemuan_ke) {
        $error = 'Semua field wajib diisi.';
    } else {
        $stmt = $pdo->prepare("UPDATE modul SET mata_praktikum_id=?, judul=?, deskripsi=?, pertemuan_ke=? WHERE id=?");
        if ($stmt->execute([$mata_praktikum_id, $judul, $deskripsi, $pertemuan_ke, $id])) {
            $success = 'Modul berhasil diupdate.';
        } else {
            $error = 'Gagal mengupdate modul.';
        }
    }
}

// Handle Hapus Modul
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Pastikan modul milik asisten ini
    $stmt = $pdo->prepare("SELECT m.id FROM modul m JOIN mata_praktikum mp ON m.mata_praktikum_id=mp.id WHERE m.id=? AND mp.asisten_id=?");
    $stmt->execute([$id, $asisten_id]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM modul WHERE id=?")->execute([$id]);
        $success = 'Modul berhasil dihapus.';
    } else {
        $error = 'Modul tidak ditemukan atau bukan milik Anda.';
    }
}

// Ambil data modul untuk edit jika ada parameter edit
$edit_modul = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT m.* FROM modul m JOIN mata_praktikum mp ON m.mata_praktikum_id=mp.id WHERE m.id=? AND mp.asisten_id=?");
    $stmt->execute([$id, $asisten_id]);
    $edit_modul = $stmt->fetch();
}

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

<!-- Pesan -->
<div class="max-w-2xl mx-auto mb-4">
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-2"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-2"><?= $error ?></div>
    <?php endif; ?>
</div>

<div class="flex justify-between items-center mb-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-900">Daftar Modul</h2>
    <button onclick="openModal('modalTambah')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>Tambah Modul
    </button>
</div>

<div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
    <?php if (empty($modul)): ?>
        <div class="text-center py-8">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Belum ada modul yang dibuat.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
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
                                <button onclick="openEditModal(<?= $m['id'] ?>)" class="text-blue-600 hover:underline"><i class="fas fa-edit"></i> Edit</button>
                                <a href="modul.php?hapus=<?= $m['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Hapus modul ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah Modul -->
<div id="modalTambah" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="closeModal('modalTambah')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        <h2 class="text-lg font-bold mb-4">Tambah Modul</h2>
        <form method="post">
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Praktikum</label>
                <select name="mata_praktikum_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Pilih Praktikum</option>
                    <?php foreach ($praktikum_list as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Judul Modul</label>
                <input type="text" name="judul" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="deskripsi" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Pertemuan Ke</label>
                <input type="number" name="pertemuan_ke" class="w-full border rounded px-3 py-2" required min="1">
            </div>
            <div>
                <button type="submit" name="tambah_modul" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
                <button type="button" onclick="closeModal('modalTambah')" class="ml-2 text-gray-600 hover:underline">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Modul -->
<?php if ($edit_modul): ?>
<div id="modalEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="window.location='modul.php'" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        <h2 class="text-lg font-bold mb-4">Edit Modul</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= $edit_modul['id'] ?>">
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Praktikum</label>
                <select name="mata_praktikum_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Pilih Praktikum</option>
                    <?php foreach ($praktikum_list as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($edit_modul['mata_praktikum_id'] == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Judul Modul</label>
                <input type="text" name="judul" class="w-full border rounded px-3 py-2" required value="<?= htmlspecialchars($edit_modul['judul']) ?>">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="deskripsi" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($edit_modul['deskripsi']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium mb-1">Pertemuan Ke</label>
                <input type="number" name="pertemuan_ke" class="w-full border rounded px-3 py-2" required min="1" value="<?= $edit_modul['pertemuan_ke'] ?>">
            </div>
            <div>
                <button type="submit" name="edit_modul" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
                <a href="modul.php" class="ml-2 text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
function openEditModal(id) {
    window.location = 'modul.php?edit=' + id;
}
// Auto open modal edit jika edit_modul aktif
<?php if ($edit_modul): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalEdit').scrollIntoView({behavior: 'smooth'});
    });
<?php endif; ?>
</script>

<?php require_once 'templates/footer.php'; ?>
