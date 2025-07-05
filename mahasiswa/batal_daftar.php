<?php
require_once '../config.php';
requireLogin();

if (!isMahasiswa()) {
    redirectTo('../asisten/dashboard.php');
}

$mahasiswa_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pendaftaran_id'])) {
    $pendaftaran_id = (int)$_POST['pendaftaran_id'];
    // Pastikan pendaftaran milik mahasiswa ini
    $stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE id = ? AND mahasiswa_id = ?");
    $stmt->execute([$pendaftaran_id, $mahasiswa_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE pendaftaran SET status = 'tidak_aktif' WHERE id = ?");
        $stmt->execute([$pendaftaran_id]);
    }
}

redirectTo('katalog.php');
?>
