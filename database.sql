-- Database: simprak
-- Buat database baru
CREATE DATABASE IF NOT EXISTS simprak;
USE simprak;

-- Tabel users (sudah ada)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel mata praktikum
CREATE TABLE `mata_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text,
  `semester` int(11) NOT NULL,
  `sks` int(11) NOT NULL,
  `asisten_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `fk_asisten` (`asisten_id`),
  FOREIGN KEY (`asisten_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel modul/pertemuan
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mata_praktikum_id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text,
  `pertemuan_ke` int(11) NOT NULL,
  `file_materi` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_mata_praktikum` (`mata_praktikum_id`),
  FOREIGN KEY (`mata_praktikum_id`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel pendaftaran mahasiswa ke praktikum
CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `mata_praktikum_id` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pendaftaran` (`mahasiswa_id`, `mata_praktikum_id`),
  KEY `fk_mahasiswa` (`mahasiswa_id`),
  KEY `fk_mata_praktikum_pendaftaran` (`mata_praktikum_id`),
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mata_praktikum_id`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel pengumpulan laporan
CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `nilai` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `status` enum('dikumpulkan','dinilai') NOT NULL DEFAULT 'dikumpulkan',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_laporan` (`mahasiswa_id`, `modul_id`),
  KEY `fk_mahasiswa_laporan` (`mahasiswa_id`),
  KEY `fk_modul_laporan` (`modul_id`),
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data contoh untuk mata_praktikum (asisten_id mengacu pada id user asisten di atas, biasanya 1 dan 2 jika database baru)
INSERT INTO `mata_praktikum` (`kode`, `nama`, `deskripsi`, `semester`, `sks`, `asisten_id`) VALUES
('PDW101', 'Praktikum Pengembangan Desain Web', 'Praktikum pengembangan aplikasi web menggunakan HTML, CSS, JavaScript, dan PHP', 5, 2, 1),
('JK201', 'Praktikum Jaringan Komputer', 'Praktikum konfigurasi dan troubleshooting jaringan komputer', 4, 2, 2);

-- Data contoh untuk modul
INSERT INTO `modul` (`mata_praktikum_id`, `judul`, `deskripsi`, `pertemuan_ke`) VALUES
(1, 'Pengenalan HTML dan CSS', 'Mempelajari dasar-dasar HTML dan CSS untuk membuat halaman web', 1),
(1, 'JavaScript Dasar', 'Mempelajari pemrograman JavaScript untuk interaktivitas web', 2),
(1, 'PHP dan MySQL', 'Mempelajari pengembangan backend menggunakan PHP dan MySQL', 3),
(2, 'Pengenalan Jaringan', 'Konsep dasar jaringan komputer dan protokol', 1),
(2, 'Konfigurasi Router', 'Praktik konfigurasi router dan switch', 2);

