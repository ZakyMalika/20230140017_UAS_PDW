<?php
require_once '../config.php';
requireLogin();

if (!isMahasiswa()) {
    redirectTo('../asisten/dashboard.php');
}

$current_user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'SIMPRAK' ?> - Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                    <h1 class="text-xl font-bold text-gray-900">SIMPRAK</h1>
                    <span class="text-sm text-gray-500">Mahasiswa</span>
                </div>
                
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="katalog.php" class="text-gray-600 hover:text-blue-600 font-medium">
                        <i class="fas fa-search mr-1"></i>Cari Praktikum
                    </a>
                    <a href="praktikum_saya.php" class="text-gray-600 hover:text-blue-600 font-medium">
                        <i class="fas fa-book mr-1"></i>Praktikum Saya
                    </a>
                </nav>

                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 focus:outline-none">
                            <i class="fas fa-user-circle text-2xl"></i>
                            <span class="hidden md:block font-medium"><?= htmlspecialchars($current_user['nama']) ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                <div class="font-medium"><?= htmlspecialchars($current_user['nama']) ?></div>
                                <div class="text-gray-500"><?= htmlspecialchars($current_user['email']) ?></div>
                            </div>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <nav class="md:hidden bg-white border-t">
        <div class="flex justify-around py-2">
            <a href="dashboard.php" class="flex flex-col items-center py-2 text-gray-600 hover:text-blue-600">
                <i class="fas fa-tachometer-alt text-lg"></i>
                <span class="text-xs mt-1">Dashboard</span>
            </a>
            <a href="katalog.php" class="flex flex-col items-center py-2 text-gray-600 hover:text-blue-600">
                <i class="fas fa-search text-lg"></i>
                <span class="text-xs mt-1">Cari</span>
            </a>
            <a href="praktikum_saya.php" class="flex flex-col items-center py-2 text-gray-600 hover:text-blue-600">
                <i class="fas fa-book text-lg"></i>
                <span class="text-xs mt-1">Praktikum</span>
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">