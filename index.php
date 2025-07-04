<?php
require_once 'config.php';

// Redirect berdasarkan role jika sudah login
if (isLoggedIn()) {
    if (isAsisten()) {
        redirectTo('asisten/dashboard.php');
    } else {
        redirectTo('mahasiswa/dashboard.php');
    }
}

// Ambil daftar mata praktikum untuk katalog publik
$stmt = $pdo->prepare("
    SELECT mp.*, u.nama as nama_asisten 
    FROM mata_praktikum mp 
    JOIN users u ON mp.asisten_id = u.id 
    ORDER BY mp.nama
");
$stmt->execute();
$mata_praktikum = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPRAK - Sistem Informasi Manajemen Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                    <h1 class="text-2xl font-bold text-gray-900">SIMPRAK</h1>
                </div>
                
                <nav class="flex items-center space-x-4">
                    <a href="#katalog" class="text-gray-600 hover:text-blue-600 font-medium">Katalog</a>
                    <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="register.php" class="border border-blue-600 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Sistem Informasi Manajemen Praktikum
            </h2>
            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                Platform terpadu untuk mengelola praktikum, materi, dan penilaian
            </p>
            <div class="flex justify-center space-x-4">
                <a href="login.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Mulai Sekarang
                </a>
                <a href="#katalog" class="border border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition">
                    Lihat Katalog
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="text-3xl font-bold text-center mb-12">Fitur Unggulan</h3>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6 rounded-lg border hover:shadow-lg transition">
                    <i class="fas fa-book-open text-4xl text-blue-600 mb-4"></i>
                    <h4 class="text-xl font-semibold mb-2">Manajemen Materi</h4>
                    <p class="text-gray-600">Upload dan kelola materi praktikum dengan mudah</p>
                </div>
                <div class="text-center p-6 rounded-lg border hover:shadow-lg transition">
                    <i class="fas fa-file-upload text-4xl text-green-600 mb-4"></i>
                    <h4 class="text-xl font-semibold mb-2">Pengumpulan Tugas</h4>
                    <p class="text-gray-600">Sistem pengumpulan laporan yang terintegrasi</p>
                </div>
                <div class="text-center p-6 rounded-lg border hover:shadow-lg transition">
                    <i class="fas fa-chart-line text-4xl text-purple-600 mb-4"></i>
                    <h4 class="text-xl font-semibold mb-2">Penilaian Online</h4>
                    <p class="text-gray-600">Berikan nilai dan feedback secara digital</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Katalog Section -->
    <section id="katalog" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="text-3xl font-bold text-center mb-12">Katalog Mata Praktikum</h3>
            
            <?php if (empty($mata_praktikum)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-xl text-gray-500">Belum ada mata praktikum yang tersedia</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($mata_praktikum as $mp): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        <?= htmlspecialchars($mp['kode']) ?>
                                    </span>
                                    <span class="text-sm text-gray-500"><?= $mp['sks'] ?> SKS</span>
                                </div>
                                
                                <h4 class="text-xl font-semibold mb-2"><?= htmlspecialchars($mp['nama']) ?></h4>
                                <p class="text-gray-600 mb-4 text-sm"><?= htmlspecialchars($mp['deskripsi']) ?></p>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-user mr-1"></i>
                                        <?= htmlspecialchars($mp['nama_asisten']) ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Semester <?= $mp['semester'] ?>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t">
                                    <a href="login.php" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition text-center block">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Mendaftar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                    <span class="text-xl font-bold">SIMPRAK</span>
                </div>
                <p class="text-gray-400">© 2024 Sistem Informasi Manajemen Praktikum. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>