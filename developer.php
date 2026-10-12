<?php
require_once 'config.php';

$query_developer = mysqli_query($conn, "SELECT * FROM developer_team WHERE nama = 'Ryuto Kazuma' ORDER BY urutan ASC LIMIT 1");
$developers = [];
if ($query_developer) {
    while ($row = mysqli_fetch_assoc($query_developer)) { $developers[] = $row; }
}
if (!$developers) {
    $developers = [
        ['nama' => 'Ryuto Kazuma', 'peran' => 'Frontend Engineer & Database Content Engineer', 'deskripsi' => 'Merancang antarmuka frontend sekaligus menjaga struktur database, konten, dan integrasi informasi website XII RPL II.', 'keahlian' => 'HTML, CSS, JavaScript, PHP, MySQL, Database Content', 'kontak' => 'Frontend & Database Content Engineer']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Only - Ryuto Kazuma</title>
    <script>document.documentElement.classList.add("preloading");</script>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="developer-page developer-only-page">
    <div id="site-splash" class="site-splash" role="status" aria-label="Memuat halaman developer">
        <div class="splash-inner">
            <div class="splash-logo"><i class="fa-solid fa-code"></i></div>
            <span class="splash-kicker">DEVELOPER PORTAL</span>
            <strong class="splash-title title-font">RYUTO KAZUMA</strong>
            <div class="splash-progress" aria-hidden="true"><span></span></div>
            <p class="splash-caption">Memuat profil developer...</p>
        </div>
    </div>

    <div class="site-shell">
        <main class="developer-only-main">
            <header class="developer-only-header">
                <span class="badge"><i class="fa-solid fa-terminal"></i> Developer Only</span>
                <h1 class="title-font">Developer<br><span>Ryuto Kazuma</span></h1>
                <p>Halaman ini khusus untuk menampilkan Ryuto Kazuma sebagai developer yang membangun, mengelola, dan mengembangkan website XII RPL II.</p>
                <div class="developer-only-code"><span>const</span> developer = <strong>"Ryuto Kazuma"</strong>;</div>
            </header>

            <section id="team" class="developer-only-team">
                <div class="section-title developer-title">
                    <span class="section-kicker">Developer Profile</span>
                    <h2>Ryuto Kazuma</h2>
                    <div class="line"></div>
                    <p class="section-lead">Frontend Engineer &amp; Database Content Engineer untuk website XII RPL II.</p>
                </div>
                <div class="container developer-grid developer-grid-large">
                    <?php foreach ($developers as $dev): ?>
                        <article class="developer-card developer-card-dark">
                            <div class="developer-photo"><div class="developer-logo-avatar" aria-label="Logo developer"><i class="fa-solid fa-code"></i></div></div>
                            <div class="developer-card-body">
                                <span class="developer-role"><?= htmlspecialchars($dev['peran']) ?></span>
                                <h3><?= htmlspecialchars($dev['nama']) ?></h3>
                                <p><?= nl2br(htmlspecialchars($dev['deskripsi'])) ?></p>
                                <?php if (!empty($dev['keahlian'])): ?><div class="developer-skills"><i class="fa-solid fa-wand-magic-sparkles"></i> <?= htmlspecialchars($dev['keahlian']) ?></div><?php endif; ?>
                                <?php if (!empty($dev['kontak'])): ?><div class="developer-contact"><i class="fa-solid fa-at"></i> <?= htmlspecialchars($dev['kontak']) ?></div><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="developer-only-back"><a href="index.php" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i> Kembali ke Website Utama</a></div>
            </section>
        </main>

        <footer class="developer-footer"><p>&copy; 2026 XII RPL II SMK PGRI 01 Sukorejo. Pengembang: <strong>Ryuto Kazuma</strong>.</p></footer>
    </div>

    <script>const playlist = [];</script>
    <script src="js/script.js?v=<?= time(); ?>"></script>
</body>
</html>
