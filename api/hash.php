<?php
require_once 'config.php';

$username       = "admin";
$password_asli  = "admin123";
$nama_lengkap   = "Ryuto Kazuma";
$password_hash  = password_hash($password_asli, PASSWORD_DEFAULT);

mysqli_query($conn, "TRUNCATE TABLE users");
$stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $password_hash, $nama_lengkap);

if ($stmt->execute()) {
    echo "<div style='font-family:sans-serif; padding:20px; background:#e8f8f5; color:#117a65; border-radius:8px;'>";
    echo "<h1>✓ Setup Akun Admin Berhasil!</h1>";
    echo "<p>Username: <b>$username</b></p>";
    echo "<p>Password: <b>$password_asli</b></p>";
    echo "<a href='login.php' style='display:inline-block; padding:10px 20px; background:#117a65; color:#fff; text-decoration:none; border-radius:5px;'>Login Sekarang</a>";
    echo "</div>";
} else {
    echo "Gagal: " . $stmt->error;
}
?>
