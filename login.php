<?php
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    if (isAsisten()) {
        redirectTo('asisten/dashboard.php');
    } else {
        redirectTo('mahasiswa/dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'asisten') {
                redirectTo('asisten/dashboard.php');
            } else {
                redirectTo('mahasiswa/dashboard.php');
            }
        } else {
            $error = 'Email atau password salah';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <i class="fas fa-graduation-cap text-5xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">SIMPRAK</h1>
            <p class="text-gray-600 mt-2">Masuk ke akun Anda</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1"></i>Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                           placeholder="masukkan email Anda">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i>Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                           placeholder="masukkan password Anda">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Belum punya akun? 
                    <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        Daftar di sini
                    </a>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Demo Account Info -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-center">Demo Account</h3>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div class="bg-blue-50 p-3 rounded">
                    <h4 class="font-semibold text-blue-800">Asisten</h4>
                    <p class="text-gray-600">Email: ahmad.fauzi@univ.ac.id</p>
                    <p class="text-gray-600">Pass: password</p>
                </div>
                <div class="bg-green-50 p-3 rounded">
                    <h4 class="font-semibold text-green-800">Mahasiswa</h4>
                    <p class="text-gray-600">Email: budi.santoso@student.univ.ac.id</p>
                    <p class="text-gray-600">Pass: password</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>