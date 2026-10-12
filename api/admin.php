<?php
require_once 'config.php';

if (!isset($_SESSION['login_admin'])) { 
    header("Location: login.php"); 
    exit; 
}

if (!isset($_SESSION['admin_id'])) {
    $lookupValue = $_SESSION['admin_username'] ?? $_SESSION['admin_name'] ?? '';
    if ($lookupValue !== '') {
        $lookupAdmin = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE username = ? OR nama_lengkap = ? LIMIT 1');
        mysqli_stmt_bind_param($lookupAdmin, 'ss', $lookupValue, $lookupValue);
        mysqli_stmt_execute($lookupAdmin);
        mysqli_stmt_bind_result($lookupAdmin, $resolvedAdminId, $resolvedAdminUsername);
        if (mysqli_stmt_fetch($lookupAdmin)) {
            $_SESSION['admin_id'] = (int)$resolvedAdminId;
            $_SESSION['admin_username'] = $resolvedAdminUsername;
        }
        mysqli_stmt_close($lookupAdmin);
    }
}

$admin_flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

function adminRedirect($anchor, $type, $text) {
    $_SESSION['admin_flash'] = ['type' => $type, 'text' => $text];
    header('Location: admin.php#' . $anchor);
    exit;
}

// --- PENGATURAN AKUN ADMIN ---
if (isset($_POST['ubah_akun'])) {
    $adminId = (int)($_SESSION['admin_id'] ?? 0);
    $usernameBaru = trim($_POST['username_baru'] ?? '');
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

    if ($adminId <= 0 || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $usernameBaru)) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.'];
    } elseif ($passwordBaru !== '' && (strlen($passwordBaru) < 8 || $passwordBaru !== $konfirmasiPassword)) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Password baru minimal 8 karakter dan konfirmasinya harus sama.'];
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
        mysqli_stmt_bind_param($check, 'si', $usernameBaru, $adminId);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        $usernameDipakai = mysqli_stmt_num_rows($check) > 0;
        mysqli_stmt_close($check);

        if ($usernameDipakai) {
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Username tersebut sudah digunakan admin lain.'];
        } else {
            if ($passwordBaru !== '') {
                $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
                $update = mysqli_prepare($conn, 'UPDATE users SET username = ?, password = ? WHERE id = ?');
                mysqli_stmt_bind_param($update, 'ssi', $usernameBaru, $hashBaru, $adminId);
            } else {
                $update = mysqli_prepare($conn, 'UPDATE users SET username = ? WHERE id = ?');
                mysqli_stmt_bind_param($update, 'si', $usernameBaru, $adminId);
            }
            $berhasil = mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
            if ($berhasil) {
                $_SESSION['admin_username'] = $usernameBaru;
                $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Username dan password admin berhasil diperbarui.'];
            } else {
                $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Pengaturan akun gagal disimpan.'];
            }
        }
    }
    header('Location: admin.php#akun');
    exit;
}

// --- PROSES ANGGOTA ---
if (isset($_POST['tambah_anggota'])) {
    $nama = bersihkan($_POST['nama']);
    $role = bersihkan($_POST['role']);
    $urutan = ($role == 'Wali Kelas') ? 1 : (($role == 'Ketua') ? 2 : (($role == 'Wakil') ? 3 : (($role == 'Sekretaris') ? 4 : (($role == 'Bendahara') ? 5 : 99))));
    
    $foto = ($role === 'Siswa') ? uploadFotoSiswa($_FILES['foto'] ?? null) : uploadFoto($_FILES['foto'] ?? null);
    if ($foto !== false) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO anggota (nama, role, foto, urutan) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $role, $foto, $urutan);
        $berhasil = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['admin_flash'] = $berhasil
            ? ['type' => 'success', 'text' => 'Anggota berhasil ditambahkan.']
            : ['type' => 'error', 'text' => 'Data anggota gagal disimpan.'];
    } else {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Format foto tidak valid atau ukuran terlalu besar.'];
    }
    header('Location: admin.php#anggota');
    exit;
}

if (isset($_GET['hapus_anggota'])) {
    $id = (int)$_GET['hapus_anggota'];
    $dt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM anggota WHERE id='$id'"));
    if($dt && $dt['foto'] != 'default.png' && file_exists('uploads/'.$dt['foto'])) {
        @unlink('uploads/'.$dt['foto']);
    }
    mysqli_query($conn, "DELETE FROM anggota WHERE id='$id'");
    header("Location: admin.php#anggota"); 
    exit;
}

// --- PROSES TIM DEVELOPER ---
if (isset($_POST['tambah_developer'])) {
    $nama = trim($_POST['nama'] ?? '');
    $peran = trim($_POST['peran'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $keahlian = trim($_POST['keahlian'] ?? '');
    $kontak = trim($_POST['kontak'] ?? '');
    $urutan = max(1, (int)($_POST['urutan'] ?? 99));
    $foto = uploadFoto($_FILES['foto'] ?? null);

    if ($nama === '' || $peran === '' || $deskripsi === '' || $foto === false) {
        adminRedirect('developer', 'error', 'Lengkapi data developer dan gunakan foto JPG/PNG/WEBP maksimal 5MB.');
    }
    $stmt = mysqli_prepare($conn, "INSERT INTO developer_team (nama, peran, deskripsi, keahlian, kontak, foto, urutan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $peran, $deskripsi, $keahlian, $kontak, $foto, $urutan);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    adminRedirect('developer', $berhasil ? 'success' : 'error', $berhasil ? 'Anggota tim developer berhasil ditambah.' : 'Data developer gagal disimpan.');
}

// --- EDIT HALAMAN KHUSUS DEVELOPER RYUTO KAZUMA ---
if (isset($_POST['ubah_halaman_developer'])) {
    $id = (int)($_POST['developer_id'] ?? 0);
    $peran = trim($_POST['peran_developer'] ?? '');
    $deskripsi = trim($_POST['deskripsi_developer'] ?? '');
    $keahlian = trim($_POST['keahlian_developer'] ?? '');
    $kontak = trim($_POST['kontak_developer'] ?? '');
    $fotoBaru = null;
    $adaUpload = isset($_FILES['foto_developer']) && ($_FILES['foto_developer']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

    if ($id <= 0 || $peran === '' || $deskripsi === '') {
        adminRedirect('developer', 'error', 'Peran dan deskripsi profil Ryuto Kazuma wajib diisi.');
    }
    if ($adaUpload) {
        $fotoBaru = uploadFoto($_FILES['foto_developer']);
        if ($fotoBaru === false) adminRedirect('developer', 'error', 'Foto developer tidak valid atau melebihi batas ukuran.');
    }

    if ($fotoBaru !== null && $fotoBaru !== 'default.png') {
        $stmt = mysqli_prepare($conn, "UPDATE developer_team SET peran = ?, deskripsi = ?, keahlian = ?, kontak = ?, foto = ? WHERE id = ? AND nama = 'Ryuto Kazuma'");
        mysqli_stmt_bind_param($stmt, 'sssssi', $peran, $deskripsi, $keahlian, $kontak, $fotoBaru, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE developer_team SET peran = ?, deskripsi = ?, keahlian = ?, kontak = ? WHERE id = ? AND nama = 'Ryuto Kazuma'");
        mysqli_stmt_bind_param($stmt, 'ssssi', $peran, $deskripsi, $keahlian, $kontak, $id);
    }
    $berhasil = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0;
    mysqli_stmt_close($stmt);
    adminRedirect('developer', $berhasil ? 'success' : 'error', $berhasil ? 'Halaman khusus developer berhasil diperbarui.' : 'Halaman khusus developer gagal diperbarui.');
}

if (isset($_GET['hapus_developer'])) {
    $id = (int)$_GET['hapus_developer'];
    $stmt = mysqli_prepare($conn, "SELECT foto FROM developer_team WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $fotoLama);
    $dt = mysqli_stmt_fetch($stmt) ? ['foto' => $fotoLama] : null;
    mysqli_stmt_close($stmt);
    if ($dt && $dt['foto'] !== 'default.png' && file_exists('uploads/' . $dt['foto'])) {
        @unlink('uploads/' . $dt['foto']);
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM developer_team WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: admin.php#developer");
    exit;
}

// --- PROSES PENGUMUMAN ---
if (isset($_POST['tambah_pengumuman'])) {
    $judul = trim($_POST['judul'] ?? '');
    $isi   = trim($_POST['isi'] ?? '');
    if ($judul === '' || $isi === '') adminRedirect('pengumuman', 'error', 'Judul dan isi pengumuman wajib diisi.');
    $stmt = mysqli_prepare($conn, 'INSERT INTO pengumuman (judul, isi) VALUES (?, ?)');
    mysqli_stmt_bind_param($stmt, 'ss', $judul, $isi);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    adminRedirect('pengumuman', $berhasil ? 'success' : 'error', $berhasil ? 'Pengumuman berhasil ditambahkan.' : 'Pengumuman gagal disimpan.');
}

if (isset($_GET['hapus_pengumuman'])) {
    $id = (int)$_GET['hapus_pengumuman'];
    mysqli_query($conn, "DELETE FROM pengumuman WHERE id='$id'");
    header("Location: admin.php#pengumuman"); 
    exit;
}

// --- PROSES JADWAL ---
if (isset($_POST['tambah_jadwal'])) {
    $hari  = trim($_POST['hari'] ?? '');
    $jam   = (int)($_POST['jam_ke'] ?? 0);
    $waktu = trim($_POST['waktu'] ?? '');
    $mp    = trim($_POST['nama_mp'] ?? '');
    $guru  = trim($_POST['kode_guru'] ?? '');
    $ruang = trim($_POST['ruang'] ?? '');
    if ($hari === '' || $jam < 1 || $waktu === '' || $mp === '' || $guru === '' || $ruang === '') adminRedirect('jadwal', 'error', 'Semua data jadwal wajib diisi.');
    $kodeMp = '-';
    $stmt = mysqli_prepare($conn, 'INSERT INTO jadwal (hari, jam_ke, waktu, nama_mp, kode_guru, ruang, kode_mp) VALUES (?, ?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sisssss', $hari, $jam, $waktu, $mp, $guru, $ruang, $kodeMp);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    adminRedirect('jadwal', $berhasil ? 'success' : 'error', $berhasil ? 'Jadwal berhasil ditambahkan.' : 'Jadwal gagal disimpan.');
}

if (isset($_GET['hapus_jadwal'])) {
    $id = (int)$_GET['hapus_jadwal'];
    mysqli_query($conn, "DELETE FROM jadwal WHERE id='$id'");
    header("Location: admin.php#jadwal"); 
    exit;
}

// --- PROSES STORY / GALERI ---
if (isset($_POST['tambah_story'])) {
    $judul = bersihkan($_POST['judul']); 
    $kat   = bersihkan($_POST['kategori']);
    $desc  = bersihkan($_POST['deskripsi']); 
    $tgl   = bersihkan($_POST['tanggal']);
    
    $foto  = uploadFoto($_FILES['foto'] ?? null);
    if ($foto !== false && $foto != 'default.png') {
        $stmt = mysqli_prepare($conn, 'INSERT INTO galeri (judul, kategori, deskripsi, tanggal, foto) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssss', $judul, $kat, $desc, $tgl, $foto);
        $berhasil = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        adminRedirect('story', $berhasil ? 'success' : 'error', $berhasil ? 'Story berhasil ditambahkan.' : 'Story gagal disimpan.');
    }
    adminRedirect('story', 'error', 'Gagal upload gambar. Gunakan JPG, PNG, atau WEBP maksimal 5MB.');
}

if (isset($_GET['hapus_story'])) {
    $id = (int)$_GET['hapus_story'];
    $dt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM galeri WHERE id='$id'"));
    if($dt && file_exists('uploads/'.$dt['foto'])) {
        @unlink('uploads/'.$dt['foto']);
    }
    mysqli_query($conn, "DELETE FROM galeri WHERE id='$id'");
    header("Location: admin.php#story"); 
    exit;
}

// --- PROSES MUSIK ---
if (isset($_POST['tambah_musik'])) {
    $judul      = bersihkan($_POST['judul']);
    $file_musik = uploadMusik($_FILES['file_musik']);
    
    if ($file_musik !== false) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO musik (judul, file_musik) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $judul, $file_musik);
        $berhasil = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        adminRedirect('musik', $berhasil ? 'success' : 'error', $berhasil ? 'Lagu berhasil ditambahkan.' : 'Lagu gagal disimpan.');
    }
    adminRedirect('musik', 'error', 'Gagal upload musik. Pastikan format MP3/WAV/OGG dan maksimal 20MB.');
}

if (isset($_GET['hapus_musik'])) {
    $id = (int)$_GET['hapus_musik'];
    $dt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_musik FROM musik WHERE id='$id'"));
    if($dt && file_exists('uploads/musik/'.$dt['file_musik'])) {
        @unlink('uploads/musik/'.$dt['file_musik']);
    }
    mysqli_query($conn, "DELETE FROM musik WHERE id='$id'");
    header("Location: admin.php#musik"); 
    exit;
}

// Query Tampil Data
$q_anggota    = mysqli_query($conn, "SELECT * FROM anggota ORDER BY urutan ASC, nama ASC");
$q_pengumuman = mysqli_query($conn, "SELECT * FROM pengumuman ORDER BY id DESC");
$q_jadwal     = mysqli_query($conn, "SELECT * FROM jadwal ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_ke ASC");
$q_story      = mysqli_query($conn, "SELECT * FROM galeri ORDER BY id DESC");
$q_musik      = mysqli_query($conn, "SELECT * FROM musik ORDER BY id DESC");
$q_developer  = mysqli_query($conn, "SELECT * FROM developer_team ORDER BY urutan ASC, nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - XII RPL II</title>
    <link rel="stylesheet" href="css/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <aside class="sidebar">
        <h2><i class="fa-solid fa-code"></i> <span>XII RPL 2</span></h2>
        <nav class="nav-menu">
            <a href="#musik"><i class="fa-solid fa-music"></i> <span>Kelola Musik</span></a>
            <a href="#pengumuman"><i class="fa-solid fa-bullhorn"></i> <span>Pengumuman</span></a>
            <a href="#jadwal"><i class="fa-solid fa-calendar-alt"></i> <span>Jadwal Kelas</span></a>
            <a href="#story"><i class="fa-solid fa-image"></i> <span>Story Class</span></a>
            <a href="#anggota"><i class="fa-solid fa-users"></i> <span>Anggota Kelas</span></a>
            <a href="#developer"><i class="fa-solid fa-code"></i> <span>Tim Developer</span></a>
            <a href="#akun"><i class="fa-solid fa-user-lock"></i> <span>Pengaturan Akun</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="index.php" target="_blank" class="btn-sub"><i class="fa-solid fa-globe"></i> <span>Lihat Web</span></a>
            <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
        </div>
    </aside>

    <main class="main-content">
        <header class="admin-header">
            <div>
                <h1>Dashboard Administrator</h1>
                <p>Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></strong>!</p>
            </div>
        </header>

        <?php if ($admin_flash): ?>
            <div class="admin-alert <?= htmlspecialchars($admin_flash['type']) ?>"><i class="fa-solid fa-<?= $admin_flash['type'] === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i> <?= htmlspecialchars($admin_flash['text']) ?></div>
        <?php endif; ?>

        <!-- PENGATURAN AKUN ADMIN -->
        <section class="card" id="akun">
            <h3><i class="fa-solid fa-user-lock"></i> Pengaturan Username &amp; Password</h3>
            <p class="admin-note">Username disimpan unik, sedangkan password baru diamankan dengan <code>password_hash</code>. Kosongkan password jika hanya ingin mengganti username.</p>
            <form action="" method="POST" class="form-grid">
                <div class="form-group">
                    <label>Username Baru</label>
                    <input type="text" name="username_baru" value="<?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?>" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Password Baru (opsional)</label>
                    <input type="password" name="password_baru" minlength="8" placeholder="Minimal 8 karakter" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" minlength="8" placeholder="Ulangi password baru" autocomplete="new-password">
                </div>
                <div class="full-width">
                    <button type="submit" name="ubah_akun" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Akun</button>
                </div>
            </form>
        </section>

        <!-- KELOLA MUSIK -->
        <section class="card" id="musik">
            <h3><i class="fa-solid fa-music"></i> Kelola Player Musik Website</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label>Judul Lagu / Artist</label>
                    <input type="text" name="judul" placeholder="Contoh: Indonesia Raya - Instrumental" required>
                </div>
                <div class="form-group">
                    <label>File Audio (MP3, WAV, OGG - Max 20MB)</label>
                    <input type="file" name="file_musik" accept="audio/*" required>
                </div>
                <div class="full-width">
                    <button type="submit" name="tambah_musik" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Lagu</button>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Judul Lagu</th><th>Nama File</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_musik) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($q_musik)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['judul']) ?></strong></td>
                                <td><code><?= htmlspecialchars($r['file_musik']) ?></code></td>
                                <td><a href="?hapus_musik=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus lagu ini?');"><i class="fa-solid fa-trash"></i> Hapus</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" align="center">Belum ada data lagu.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- KELOLA PENGUMUMAN -->
        <section class="card" id="pengumuman">
            <h3><i class="fa-solid fa-bullhorn"></i> Kelola Pengumuman</h3>
            <form action="" method="POST" class="form-grid">
                <div class="form-group full-width">
                    <label>Judul Pengumuman</label>
                    <input type="text" name="judul" placeholder="Judul Pengumuman..." required>
                </div>
                <div class="form-group full-width">
                    <label>Isi Pengumuman</label>
                    <textarea name="isi" placeholder="Detail informasi pengumuman..." required></textarea>
                </div>
                <div class="full-width">
                    <button type="submit" name="tambah_pengumuman" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Terbitkan Pengumuman</button>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th>Judul Pengumuman</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_pengumuman) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($q_pengumuman)): ?>
                            <tr>
                                <td><?= date('d M Y, H:i', strtotime($r['tanggal'])) ?></td>
                                <td><strong><?= htmlspecialchars($r['judul']) ?></strong></td>
                                <td><a href="?hapus_pengumuman=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pengumuman ini?');"><i class="fa-solid fa-trash"></i> Hapus</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" align="center">Belum ada pengumuman.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- KELOLA JADWAL -->
        <section class="card" id="jadwal">
            <h3><i class="fa-solid fa-calendar-days"></i> Kelola Jadwal Pelajaran</h3>
            <form action="" method="POST" class="form-grid">
                <div class="form-group">
                    <label>Hari</label>
                    <select name="hari">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Ke-</label>
                    <input type="number" name="jam_ke" min="1" max="12" placeholder="1" required>
                </div>
                <div class="form-group">
                    <label>Rentang Waktu</label>
                    <input type="text" name="waktu" placeholder="07:00 - 08:30" required>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <input type="text" name="nama_mp" placeholder="Pemrograman Web" required>
                </div>
                <div class="form-group">
                    <label>Kode / Nama Guru</label>
                    <input type="text" name="kode_guru" placeholder="Pak Budi / Kode: BD" required>
                </div>
                <div class="form-group">
                    <label>Ruangan</label>
                    <input type="text" name="ruang" placeholder="Lab RPL 2" required>
                </div>
                <div class="full-width">
                    <button type="submit" name="tambah_jadwal" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Jadwal</button>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Hari</th><th>Jam Ke</th><th>Waktu</th><th>Mata Pelajaran</th><th>Guru</th><th>Ruang</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_jadwal) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($q_jadwal)): ?>
                            <tr>
                                <td><span class="badge-day"><?= $r['hari'] ?></span></td>
                                <td><?= $r['jam_ke'] ?></td>
                                <td><?= htmlspecialchars($r['waktu']) ?></td>
                                <td><strong><?= htmlspecialchars($r['nama_mp']) ?></strong></td>
                                <td><?= htmlspecialchars($r['kode_guru']) ?></td>
                                <td><?= htmlspecialchars($r['ruang']) ?></td>
                                <td><a href="?hapus_jadwal=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item jadwal ini?');"><i class="fa-solid fa-trash"></i> Hapus</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" align="center">Belum ada data jadwal.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- KELOLA STORY -->
        <section class="card" id="story">
            <h3><i class="fa-solid fa-camera-retro"></i> Kelola Story / Galeri Momen</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label>Judul Momen</label>
                    <input type="text" name="judul" placeholder="Kunjungan Industri 2026" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="kategori" placeholder="Kegiatan / Classmeeting / Hobi" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Momen</label>
                    <input type="date" name="tanggal" required>
                </div>
                <div class="form-group">
                    <label>Foto Dokumentasi</label>
                    <input type="file" name="foto" accept="image/*" required>
                </div>
                <div class="form-group full-width">
                    <label>Deskripsi Singkat</label>
                    <textarea name="deskripsi" placeholder="Ceritakan kisah singkat dibalik momen ini..."></textarea>
                </div>
                <div class="full-width">
                    <button type="submit" name="tambah_story" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Unggah Story</button>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Foto</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_story) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($q_story)): ?>
                            <tr>
                                <td><img src="uploads/<?= $r['foto'] ?>" class="thumb-img" alt="thumb"></td>
                                <td><strong><?= htmlspecialchars($r['judul']) ?></strong></td>
                                <td><?= htmlspecialchars($r['kategori']) ?></td>
                                <td><?= $r['tanggal'] ?></td>
                                <td><a href="?hapus_story=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus foto ini?');"><i class="fa-solid fa-trash"></i> Hapus</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" align="center">Belum ada story kelas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- KELOLA TIM DEVELOPER -->
        <section class="card" id="developer">
            <h3><i class="fa-solid fa-code"></i> Kelola Tim Developer</h3>
            <p class="admin-note">Tim Frontend dan Tim Data tampil di web utama. Halaman khusus developer hanya membaca profil Ryuto Kazuma.</p>

            <?php $ryuto_developer = null; $q_ryuto = mysqli_query($conn, "SELECT * FROM developer_team WHERE nama = 'Ryuto Kazuma' LIMIT 1"); if ($q_ryuto) $ryuto_developer = mysqli_fetch_assoc($q_ryuto); ?>
            <?php if ($ryuto_developer): ?>
                <div class="developer-edit-box">
                    <h4><i class="fa-solid fa-pen-to-square"></i> Ubah Halaman Khusus Developer</h4>
                    <p class="admin-note">Form ini hanya mengubah halaman khusus <strong>Ryuto Kazuma</strong>. Nama tetap dikunci agar profil developer utama tidak berubah.</p>
                    <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                        <input type="hidden" name="developer_id" value="<?= (int)$ryuto_developer['id'] ?>">
                        <div class="form-group">
                            <label>Nama Developer</label>
                            <input type="text" value="Ryuto Kazuma" readonly>
                        </div>
                        <div class="form-group">
                            <label>Peran</label>
                            <input type="text" name="peran_developer" value="<?= htmlspecialchars($ryuto_developer['peran']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Keahlian</label>
                            <input type="text" name="keahlian_developer" value="<?= htmlspecialchars($ryuto_developer['keahlian'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Kontak / Label</label>
                            <input type="text" name="kontak_developer" value="<?= htmlspecialchars($ryuto_developer['kontak'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Ganti Logo / Foto (Opsional)</label>
                            <input type="file" name="foto_developer" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="form-group full-width">
                            <label>Deskripsi Halaman Khusus</label>
                            <textarea name="deskripsi_developer" required><?= htmlspecialchars($ryuto_developer['deskripsi']) ?></textarea>
                        </div>
                        <div class="full-width">
                            <button type="submit" name="ubah_halaman_developer" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Halaman Developer</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <p class="admin-note">Profil Ryuto Kazuma belum ada. Tambahkan profil tersebut melalui form di bawah agar halaman khusus dapat diedit.</p>
            <?php endif; ?>

            <h4 class="admin-subheading">Tambah Anggota Tim Homepage</h4>
            <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label>Nama / Nama Tim</label>
                    <input type="text" name="nama" placeholder="Ryuto Kazuma / Tim Frontend" required>
                </div>
                <div class="form-group">
                    <label>Peran</label>
                    <input type="text" name="peran" placeholder="Lead Fullstack Developer" required>
                </div>
                <div class="form-group">
                    <label>Keahlian</label>
                    <input type="text" name="keahlian" placeholder="PHP, MySQL, UI/UX">
                </div>
                <div class="form-group">
                    <label>Kontak / Label</label>
                    <input type="text" name="kontak" placeholder="Frontend Team / @username">
                </div>
                <div class="form-group">
                    <label>Urutan Tampil</label>
                    <input type="number" name="urutan" min="1" max="99" value="99" required>
                </div>
                <div class="form-group">
                    <label>Foto Profil (Opsional)</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="form-group full-width">
                    <label>Deskripsi Peran</label>
                    <textarea name="deskripsi" placeholder="Ceritakan kontribusi developer ini..." required></textarea>
                </div>
                <div class="full-width">
                    <button type="submit" name="tambah_developer" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Tambah Developer</button>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Profil</th><th>Nama</th><th>Peran</th><th>Keahlian</th><th>Urutan</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php if ($q_developer && mysqli_num_rows($q_developer) > 0): ?>
                            <?php while ($r = mysqli_fetch_assoc($q_developer)): ?>
                                <tr>
                                    <td><img src="uploads/<?= htmlspecialchars($r['foto']) ?>" class="thumb-avatar" alt="avatar"></td>
                                    <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                                    <td><span class="role-badge role-developer"><?= htmlspecialchars($r['peran']) ?></span></td>
                                    <td><?= htmlspecialchars($r['keahlian'] ?: '-') ?></td>
                                    <td><?= (int)$r['urutan'] ?></td>
                                    <td><a href="?hapus_developer=<?= (int)$r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus anggota developer ini?');"><i class="fa-solid fa-trash"></i> Hapus</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" align="center">Belum ada data tim developer.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- KELOLA ANGGOTA -->
        <section class="card" id="anggota">
            <h3><i class="fa-solid fa-users-gear"></i> Kelola Anggota & Pengurus Kelas</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama siswa / wali kelas..." required>
                </div>
                <div class="form-group">
                    <label>Jabatan / Role</label>
                    <select name="role">
                        <option value="Siswa">Siswa Biasa</option>
                        <option value="Wali Kelas">Wali Kelas</option>
                        <option value="Ketua">Ketua Kelas</option>
                        <option value="Wakil">Wakil Ketua</option>
                        <option value="Sekretaris">Sekretaris</option>
                        <option value="Bendahara">Bendahara</option>
                    </select>
                </div>
                <div class="form-group full-width">
                            <label>Foto Profil (Opsional; siswa disimpan ke uploads/siswa)</label>
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="full-width">
                    <button type="submit" name="tambah_anggota" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Tambah Anggota</button>
                </div>
            </form>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Nama Lengkap</th><th>Role</th><th>Foto</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_anggota) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($q_anggota)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                                <td><span class="role-badge role-<?= strtolower(explode(' ', $r['role'])[0]) ?>"><?= $r['role'] ?></span></td>
                                <td><img src="uploads/<?= $r['foto'] ?>" class="thumb-avatar" alt="avatar"></td>
                                <td><a href="?hapus_anggota=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus anggota ini?');"><i class="fa-solid fa-trash"></i> Hapus</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" align="center">Belum ada data anggota.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>
