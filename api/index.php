<?php
require_once 'config.php';

// Safe Querying
$query_pengurus  = mysqli_query($conn, "SELECT * FROM anggota WHERE role != 'Siswa' ORDER BY urutan ASC");
$query_siswa     = mysqli_query($conn, "SELECT * FROM anggota WHERE role = 'Siswa' ORDER BY nama ASC");
$query_pengumuman= mysqli_query($conn, "SELECT * FROM pengumuman ORDER BY id DESC LIMIT 5");
$query_story     = mysqli_query($conn, "SELECT * FROM galeri ORDER BY tanggal DESC LIMIT 6");
$query_developer = mysqli_query($conn, "SELECT * FROM developer_team ORDER BY urutan ASC, nama ASC LIMIT 6");
$developer_preview = [];
if ($query_developer) {
    while ($dev = mysqli_fetch_assoc($query_developer)) { $developer_preview[] = $dev; }
}
if (count($developer_preview) === 0) {
    $developer_preview = [
        ['nama' => 'Ryuto Kazuma', 'peran' => 'Frontend Engineer & Database Content Engineer', 'deskripsi' => 'Merancang antarmuka frontend sekaligus menjaga struktur database, konten, dan integrasi informasi website.', 'keahlian' => 'HTML, CSS, JavaScript, PHP, MySQL, Database Content', 'foto' => 'default.png'],
        ['nama' => 'Tim Frontend', 'peran' => 'Frontend Engineer', 'deskripsi' => 'Membangun tampilan responsif yang nyaman dipakai semua pengunjung.', 'keahlian' => 'HTML, CSS, JavaScript', 'foto' => 'default.png'],
        ['nama' => 'Tim Data', 'peran' => 'Database & Content Engineer', 'deskripsi' => 'Menata data, konten, dan dokumentasi agar website tetap informatif.', 'keahlian' => 'MySQL, Data Management', 'foto' => 'default.png']
    ];
}

$query_jadwal = mysqli_query($conn, "SELECT * FROM jadwal ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_ke ASC");
$jadwal_per_hari = [];
if ($query_jadwal) {
    while ($row = mysqli_fetch_assoc($query_jadwal)) { 
        $jadwal_per_hari[$row['hari']][] = $row; 
    }
}

// Ambil Playlist Musik
$query_musik = mysqli_query($conn, "SELECT * FROM musik ORDER BY id DESC");
$playlist = [];
if ($query_musik) {
    while($m = mysqli_fetch_assoc($query_musik)){
            $playlist[] = [
                'id'    => (int)$m['id'],
                'judul' => $m['judul'],
                'file'  => 'uploads/musik/' . $m['file_musik']
            ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XII RPL II - SMK PGRI 01 Sukorejo</title>
    <script>document.documentElement.classList.add("preloading");</script>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div id="site-splash" class="site-splash" role="status" aria-label="Memuat website">
        <div class="splash-inner">
            <div class="splash-logo"><i class="fa-solid fa-code"></i></div>
            <span class="splash-kicker">INITIALIZING CLASS PORTAL</span>
            <strong class="splash-title title-font">XII RPL II</strong>
            <div class="splash-progress" aria-hidden="true"><span></span></div>
            <p class="splash-caption">Mempersiapkan ruang digital kelas...</p>
        </div>
    </div>

    <!-- Widget Modern Music Player: sengaja berada di luar site-shell agar fixed ke viewport -->
    <div class="music-player-widget">
        <div class="music-icon"><i class="fa-solid fa-compact-disc" id="disc-icon"></i></div>
        <div class="music-info">
            <div class="music-title" id="music-title">Memuat playlist...</div>
            <select id="track-select" class="music-track-select" aria-label="Pilih lagu untuk diputar">
                <?php if (count($playlist) > 0): ?>
                    <?php foreach ($playlist as $track): ?>
                        <option value="<?= (int)$track['id'] ?>"><?= htmlspecialchars($track['judul']) ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled selected>Playlist Kosong</option>
                <?php endif; ?>
            </select>
            <div class="music-status" id="music-status">Ready</div>
        </div>
        <div class="music-controls">
            <button class="music-btn" id="prev-btn" title="Sebelumnya"><i class="fa-solid fa-backward-step"></i></button>
            <button class="music-btn" id="play-btn" title="Putar"><i class="fa-solid fa-play"></i></button>
            <button class="music-btn" id="next-btn" title="Berikutnya"><i class="fa-solid fa-forward-step"></i></button>
        </div>
    </div>
    <audio id="audio-player" preload="metadata"></audio>

    <div class="site-shell">

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-brand title-font">
            <i class="fa-solid fa-code"></i> XII RPL II
        </div>
        <ul class="nav-links">
            <li><a href="#beranda">Beranda</a></li>
            <li><a href="#profil">Profil</a></li>
            <li><a href="#visi-misi">Visi Misi</a></li>
            <li><a href="#pengumuman">Pengumuman</a></li>
            <li><a href="#jadwal">Jadwal</a></li>
            <li><a href="#struktur">Siswa</a></li>
            <li><a href="#story">Story</a></li>
            <?php if (isset($_SESSION['login_admin'])): ?>
                <li><a href="admin.php" class="btn btn-nav-admin"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn btn-nav-login"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Section Hero (Beranda) -->
    <section id="beranda" class="hero hidden-anim">
        <div class="hero-content">
            <span class="badge">Official Class Website</span>
            <h1 class="title-font">XII RPL II</h1>
            <h2>SMK PGRI 01 Sukorejo</h2>
            <p>Mencetak generasi developer muda yang kreatif, inovatif, berakhlak mulia, dan siap bersaing di dunia teknologi digital.</p>
            <div class="hero-buttons">
                <a href="#jadwal" class="btn btn-primary">Lihat Jadwal <i class="fa-solid fa-calendar-check"></i></a>
                <a href="#profil" class="btn btn-secondary">Tentang Kami <i class="fa-solid fa-arrow-down"></i></a>
            </div>
        </div>
    </section>

    <!-- Section Profil -->
    <section id="profil" class="section hidden-anim">
        <div class="container">
            <div class="section-title">
                <h2>Profil & Aktivitas</h2>
                <div class="line"></div>
            </div>
            <div class="profil-grid">
                <div>
                    <h3>Kompetensi Keahlian XII RPL 2</h3>
                    <p>Kelas kami berfokus pada penguasaan Web Development, Mobile Apps, Database Architecture, dan Software Engineering. Kami menjunjung tinggi integritas, kerjasama tim, serta keunggulan karya.</p>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fa-solid fa-users"></i>
                        <h4>35</h4>
                        <p>Siswa</p>
                    </div>
                    <div class="stat-card">
                        <i class="fa-solid fa-laptop-code"></i>
                        <h4>20+</h4>
                        <p>Projek Selesai</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Visi Misi & Nilai -->
    <section id="visi-misi" class="section bg-light hidden-anim">
        <div class="container">
            <div class="section-title">
                <span class="section-kicker">Arah Perjalanan Kelas</span>
                <h2>Visi, Misi & Nilai Kami</h2>
                <div class="line"></div>
                <p class="section-lead">Website ini menjadi ruang bersama untuk belajar, berkarya, berbagi informasi, dan mendokumentasikan perjalanan XII RPL II.</p>
            </div>
            <div class="vision-grid">
                <article class="vision-card vision-main">
                    <div class="vision-icon"><i class="fa-solid fa-bullseye"></i></div>
                    <div>
                        <h3>Visi</h3>
                        <p>Menjadi komunitas pembelajar teknologi yang kreatif, berintegritas, kolaboratif, dan siap memberi solusi di dunia digital.</p>
                    </div>
                </article>
                <article class="vision-card">
                    <div class="vision-icon"><i class="fa-solid fa-compass"></i></div>
                    <h3>Misi</h3>
                    <ul class="mission-list">
                        <li><i class="fa-solid fa-check"></i> Membangun budaya belajar dan berbagi pengetahuan.</li>
                        <li><i class="fa-solid fa-check"></i> Menghasilkan karya digital yang bermanfaat.</li>
                        <li><i class="fa-solid fa-check"></i> Menjaga komunikasi, tanggung jawab, dan etika.</li>
                    </ul>
                </article>
            </div>
            <div class="value-grid">
                <div class="value-item"><i class="fa-solid fa-lightbulb"></i><span><strong>Kreatif</strong><small>Berani mengeksplorasi ide baru.</small></span></div>
                <div class="value-item"><i class="fa-solid fa-people-group"></i><span><strong>Kolaboratif</strong><small>Tumbuh melalui kerja tim.</small></span></div>
                <div class="value-item"><i class="fa-solid fa-shield-heart"></i><span><strong>Berintegritas</strong><small>Berkarya dengan tanggung jawab.</small></span></div>
            </div>
        </div>
    </section>

    <!-- Section Pengumuman -->
    <section id="pengumuman" class="section bg-light hidden-anim">
        <div class="container">
            <div class="section-title">
                <h2>Pengumuman Kelas</h2>
                <div class="line"></div>
            </div>
            <div class="pengumuman-grid">
                <?php if ($query_pengumuman && mysqli_num_rows($query_pengumuman) > 0): ?>
                    <?php while($p = mysqli_fetch_assoc($query_pengumuman)): ?>
                        <div class="pengumuman-card">
                            <div class="p-date"><i class="fa-solid fa-clock"></i> <?= date('d M Y, H:i', strtotime($p['tanggal'])); ?></div>
                            <h4><?= htmlspecialchars($p['judul']); ?></h4>
                            <p><?= nl2br(htmlspecialchars($p['isi'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="full-width" style="text-align: center; color: #888; grid-column: 1/-1;">
                        <p>Belum ada pengumuman terbaru saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section Jadwal -->
    <section id="jadwal" class="section hidden-anim">
        <div class="container">
            <div class="section-title">
                <h2>Jadwal Pelajaran</h2>
                <div class="line"></div>
            </div>
            <div class="schedule-tabs">
                <?php $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; foreach ($days as $day): ?>
                    <button class="tab-btn" data-day="<?= $day ?>"><?= $day ?></button>
                <?php endforeach; ?>
            </div>
            <div class="schedule-content">
                <?php foreach ($days as $day): ?>
                    <div class="tab-pane" id="day-<?= $day; ?>">
                        <?php if (isset($jadwal_per_hari[$day]) && count($jadwal_per_hari[$day]) > 0): ?>
                            <div class="table-responsive">
                                <table class="schedule-table">
                                    <thead>
                                        <tr>
                                            <th>Jam Ke</th>
                                            <th>Waktu</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Guru</th>
                                            <th>Ruang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($jadwal_per_hari[$day] as $j): ?>
                                            <tr>
                                                <td><?= $j['jam_ke']; ?></td>
                                                <td><?= htmlspecialchars($j['waktu']); ?></td>
                                                <td><strong><?= htmlspecialchars($j['nama_mp']); ?></strong></td>
                                                <td><?= htmlspecialchars($j['kode_guru']); ?></td>
                                                <td><?= htmlspecialchars($j['ruang']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; background: #fff; border-radius: 12px; color: #777;">
                                <i class="fa-solid fa-mug-hot" style="font-size: 2rem; color: var(--accent); margin-bottom: 10px;"></i>
                                <p>Tidak ada jadwal pelajaran untuk hari <?= $day ?>.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Section Struktur & Siswa -->
    <section id="struktur" class="section bg-light hidden-anim">
        <div class="container">
            <div class="section-title">
                <h2>Pengurus Kelas</h2>
                <div class="line"></div>
            </div>
            <div class="pengurus-grid">
                <?php if ($query_pengurus && mysqli_num_rows($query_pengurus) > 0): ?>
                    <?php while($p = mysqli_fetch_assoc($query_pengurus)): ?>
                    <div class="p-card">
                        <div class="p-photo">
                            <img src="uploads/<?= htmlspecialchars($p['foto']) ?>" onerror="this.src='https://via.placeholder.com/150';" alt="Foto Pengurus">
                        </div>
                        <h4><?= htmlspecialchars($p['nama']) ?></h4>
                        <span class="role-badge role-pengurus"><?= htmlspecialchars($p['role']) ?></span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #777;">Data pengurus belum diisi.</p>
                <?php endif; ?>
            </div>

            <hr style="margin: 4rem 0 3rem; border:0; border-top:1px solid #ddd;">

            <div class="section-title">
                <h2>Siswa & Siswi</h2>
                <div class="line"></div>
            </div>
            <div class="siswa-grid">
                <?php if ($query_siswa && mysqli_num_rows($query_siswa) > 0): ?>
                    <?php while($s = mysqli_fetch_assoc($query_siswa)): ?>
                        <div class="siswa-card">
                            <div class="siswa-photo">
                                <img src="uploads/<?= htmlspecialchars($s['foto']) ?>" onerror="this.src='https://via.placeholder.com/100';" alt="Foto Siswa">
                            </div>
                            <h5><?= htmlspecialchars($s['nama']); ?></h5>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #777;">Data siswa belum diisi.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section Story Class -->
    <section id="story" class="section hidden-anim">
        <div class="container">
            <div class="section-title">
                <h2>Story Class & Galeri</h2>
                <div class="line"></div>
            </div>
            <div class="story-grid">
                <?php if ($query_story && mysqli_num_rows($query_story) > 0): ?>
                    <?php while($m = mysqli_fetch_assoc($query_story)): ?>
                    <div class="story-card">
                        <div class="story-img">
                            <img src="uploads/<?= htmlspecialchars($m['foto']) ?>" onerror="this.src='https://via.placeholder.com/400x250';" alt="Story Image">
                        </div>
                        <div class="story-content">
                            <small style="color:var(--accent); font-weight:bold;"><?= htmlspecialchars($m['tanggal']) ?> - <?= htmlspecialchars($m['kategori']) ?></small>
                            <h4 style="margin:8px 0; color:var(--primary);"><?= htmlspecialchars($m['judul']) ?></h4>
                            <p><?= nl2br(htmlspecialchars($m['deskripsi'])) ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #777;">Belum ada momen terunggah.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <!-- Section Tim Developer -->
    <section id="developer" class="section hidden-anim">
        <div class="container">
            <div class="section-title">
                <span class="section-kicker">Behind The Website</span>
                <h2>Tim Pengembang</h2>
                <div class="line"></div>
                <p class="section-lead">Kenali orang-orang yang merancang, membangun, dan merawat ekosistem digital kelas ini.</p>
            </div>
            <div class="developer-grid">
                <?php foreach ($developer_preview as $dev): ?>
                    <article class="developer-card">
                        <div class="developer-photo">
                            <div class="developer-logo-avatar" aria-label="Logo tim developer"><i class="fa-solid fa-code"></i></div>
                        </div>
                        <div class="developer-card-body">
                            <span class="developer-role"><?= htmlspecialchars($dev['peran']) ?></span>
                            <h3><?= htmlspecialchars($dev['nama']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($dev['deskripsi'])) ?></p>
                            <?php if (!empty($dev['keahlian'])): ?><div class="developer-skills"><i class="fa-solid fa-wand-magic-sparkles"></i> <?= htmlspecialchars($dev['keahlian']) ?></div><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <footer style="background:var(--bg-dark); color:#fff; text-align:center; padding:2rem; margin-top:2rem;">
        <p>&copy; 2026 XII RPL II SMK PGRI 01 Sukorejo. All Rights Reserved. Pengembang: Ryuto Kazuma.</p>
        <a href="developer.php" class="footer-developer-link"><i class="fa-solid fa-code"></i> Halaman Khusus Developer</a>
    </footer>

    </div>

    <!-- Export Array Playlist PHP ke JS secara Aman -->
    <script>
        const playlist = <?= json_encode($playlist, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
    <script src="js/script.js?v=<?= time(); ?>"></script>
</body>
</html>
