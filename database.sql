CREATE DATABASE IF NOT EXISTS `db_rpl2`;
USE `db_rpl2`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL
);

INSERT INTO `users` (`username`, `password`, `nama_lengkap`) VALUES 
('admin', '$2y$10$tZ9112k/TXZlV..o10j8.e/KjN1Hw34vYF030i2P.V.0a5Z9r9W9q', 'Ryuto Kazuma - Ketua Kelas')
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `jadwal` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
  `jam_ke` INT NOT NULL,
  `waktu` VARCHAR(30) NOT NULL,
  `kode_mp` VARCHAR(20) NOT NULL DEFAULT '-',
  `nama_mp` VARCHAR(100) NOT NULL,
  `kode_guru` VARCHAR(20) NOT NULL,
  `ruang` VARCHAR(30) NOT NULL
);

CREATE TABLE IF NOT EXISTS `pengumuman` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `isi` TEXT NOT NULL,
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `anggota` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(150) NOT NULL,
  `role` ENUM('Wali Kelas', 'Ketua', 'Wakil', 'Sekretaris', 'Bendahara', 'Siswa') NOT NULL,
  `deskripsi` VARCHAR(255) DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT 'default.png',
  `urutan` INT DEFAULT 99
);

CREATE TABLE IF NOT EXISTS `galeri` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `kategori` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `foto` VARCHAR(255) NOT NULL,
  `tanggal` DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS `musik` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `file_musik` VARCHAR(255) NOT NULL
);


CREATE TABLE IF NOT EXISTS `developer_team` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(150) NOT NULL,
  `peran` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `keahlian` VARCHAR(255) DEFAULT NULL,
  `kontak` VARCHAR(150) DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT 'default.png',
  `urutan` INT DEFAULT 99,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `developer_team` (`nama`, `peran`, `deskripsi`, `keahlian`, `kontak`, `urutan`) VALUES
('Ryuto Kazuma', 'Frontend Engineer & Database Content Engineer', 'Merancang antarmuka frontend sekaligus menjaga struktur database, konten, dan integrasi informasi website kelas.', 'HTML, CSS, JavaScript, PHP, MySQL, Database Content', 'Frontend & Database Content Engineer', 1),
('Tim Frontend', 'Frontend Engineer', 'Menerjemahkan ide menjadi antarmuka yang responsif, informatif, dan mudah digunakan.', 'HTML, CSS, JavaScript, Responsive Design', 'Frontend Team', 2),
('Tim Data', 'Database & Content Engineer', 'Menjaga struktur data, konten digital, dan integrasi informasi agar tetap rapi dan konsisten.', 'MySQL, Data Management, Documentation', 'Data Team', 3);
