<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_rpl2";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { 
    die("Koneksi Database Gagal: " . mysqli_connect_error()); 
}

mysqli_set_charset($conn, 'utf8mb4');

// Tabel developer dibuat otomatis agar instalasi lama tetap kompatibel.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS developer_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    peran VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    keahlian VARCHAR(255) DEFAULT NULL,
    kontak VARCHAR(150) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT 'default.png',
    urutan INT DEFAULT 99,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Buat folder upload otomatis jika belum ada
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
if (!is_dir('uploads/musik')) {
    mkdir('uploads/musik', 0777, true);
}
if (!is_dir('uploads/siswa')) {
    mkdir('uploads/siswa', 0777, true);
}

function bersihkan($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

function uploadFoto($file) {
    if (!isset($file) || $file['error'] === 4) return 'default.png';

    $namaFile   = $file['name'];
    $ukuranFile = $file['size'];
    $tmpName    = $file['tmp_name'];

    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
    $ekstensiFile  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (!in_array($ekstensiFile, $ekstensiValid)) return false;
    if ($ukuranFile > 5000000) return false; // Maksimal 5MB

    $namaBaru = uniqid('img_', true) . '.' . $ekstensiFile;
    if (move_uploaded_file($tmpName, 'uploads/' . $namaBaru)) {
        return $namaBaru;
    }
    return false;
}

function uploadFotoSiswa($file) {
    if (!isset($file) || $file['error'] === 4) return 'default.png';

    $namaFile   = $file['name'];
    $ukuranFile = $file['size'];
    $tmpName    = $file['tmp_name'];

    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
    $ekstensiFile  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (!in_array($ekstensiFile, $ekstensiValid)) return false;
    if ($ukuranFile > 5000000) return false;

    $namaBaru = uniqid('siswa_', true) . '.' . $ekstensiFile;
    if (move_uploaded_file($tmpName, 'uploads/siswa/' . $namaBaru)) {
        return 'siswa/' . $namaBaru;
    }
    return false;
}

function uploadMusik($file) {
    if (!isset($file) || $file['error'] === 4) return false;

    $namaFile   = $file['name'];
    $ukuranFile = $file['size'];
    $tmpName    = $file['tmp_name'];

    $ekstensiValid = ['mp3', 'wav', 'ogg'];
    $ekstensiFile  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (!in_array($ekstensiFile, $ekstensiValid)) return false;
    if ($ukuranFile > 20000000) return false; // Maksimal 20MB

    $namaBaru = uniqid('audio_', true) . '.' . $ekstensiFile;
    if (move_uploaded_file($tmpName, 'uploads/musik/' . $namaBaru)) {
        return $namaBaru;
    }
    return false;
}
?>
