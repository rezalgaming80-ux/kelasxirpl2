document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const body = document.body;
    const splash = document.getElementById('site-splash');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let entranceFinished = false;

    // One entrance sequence owns splash + page reveal; scroll animations start only after it completes.
    const finishEntrance = () => {
        if (entranceFinished) return;
        entranceFinished = true;
        body.classList.add('site-ready');
        if (splash) splash.classList.add('is-hidden');
        root.classList.remove('preloading');
        body.classList.add('js-loaded');
        initScrollReveal();
    };

    // Jangan menggantungkan splash pada window.load: font, ikon, atau koneksi mobile
    // dapat membuat event tersebut terlambat/tidak selesai. DOM siap + fallback timeout
    // menjadi dua jalur independen untuk melepas splash.
    const entranceDelay = reducedMotion ? 80 : 950;
    window.setTimeout(finishEntrance, reducedMotion ? 120 : 2200);
    window.addEventListener('load', () => window.setTimeout(finishEntrance, entranceDelay), { once: true });
    if (document.readyState === 'complete') window.setTimeout(finishEntrance, entranceDelay);

    function initScrollReveal() {
        const sections = document.querySelectorAll('.hidden-anim');
        if (!('IntersectionObserver' in window)) {
            sections.forEach(section => section.classList.add('show-anim'));
            return;
        }
        const observer = new IntersectionObserver((entries, observerRef) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show-anim');
                    observerRef.unobserve(entry.target);
                }
            });
        }, { root: null, rootMargin: '0px 0px -5% 0px', threshold: 0.1 });
        sections.forEach(section => observer.observe(section));
    }

    // Jadwal tetap berjalan seperti sebelumnya.
    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    let today = dayNames[new Date().getDay()];
    if (today === 'Minggu') today = 'Senin';
    const activateTab = (day) => {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.day === day));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.toggle('active', pane.id === `day-${day}`));
    };
    if (document.querySelector('.tab-btn')) {
        activateTab(today);
        document.querySelectorAll('.tab-btn').forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.day)));
    }

    // Player musik tetap kompatibel, tetapi aman ketika dipakai di halaman developer.
    const audio = document.getElementById('audio-player');
    const playBtn = document.getElementById('play-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const titleEl = document.getElementById('music-title');
    const statusEl = document.getElementById('music-status');
    const trackSelect = document.getElementById('track-select');
    const discIcon = document.getElementById('disc-icon');
    const tracks = (typeof playlist !== 'undefined' && Array.isArray(playlist)) ? playlist : [];
    let currentTrack = 0;
    let isPlaying = false;

    const updateButton = () => { if (playBtn) playBtn.innerHTML = `<i class="fa-solid fa-${isPlaying ? 'pause' : 'play'}"></i>`; };
    const loadTrack = (index) => {
        if (!audio || !tracks[index]) return;
        currentTrack = index;
        audio.src = tracks[index].file;
        if (titleEl) titleEl.innerText = tracks[index].judul;
        if (trackSelect && trackSelect.value !== String(tracks[index].id)) trackSelect.value = String(tracks[index].id);
        if (statusEl) statusEl.innerText = 'Ready';
    };
    const playTrack = () => {
        if (!audio || tracks.length === 0) return;
        const promise = audio.play();
        if (promise !== undefined) promise.then(() => {
            isPlaying = true; updateButton();
            if (statusEl) { statusEl.innerText = 'Memutar...'; statusEl.style.color = 'var(--cyan)'; }
            if (discIcon) discIcon.classList.add('spin-anim');
        }).catch(() => {
            if (statusEl) { statusEl.innerText = 'Klik play lagi'; statusEl.style.color = '#ff6b6b'; }
            isPlaying = false; updateButton();
        });
    };
    const pauseTrack = () => {
        if (!audio) return;
        audio.pause(); isPlaying = false; updateButton();
        if (statusEl) { statusEl.innerText = 'Jeda (Paused)'; statusEl.style.color = '#aaa'; }
        if (discIcon) discIcon.classList.remove('spin-anim');
    };

    if (tracks.length > 0) loadTrack(currentTrack);
    else if (titleEl && statusEl) { titleEl.innerText = 'Tidak ada lagu'; statusEl.innerText = 'Playlist Kosong'; }
    if (playBtn) playBtn.addEventListener('click', () => isPlaying ? pauseTrack() : playTrack());
    if (trackSelect) trackSelect.addEventListener('change', () => {
        const selectedId = trackSelect.value;
        const selectedIndex = tracks.findIndex(track => String(track.id) === selectedId);
        if (selectedIndex >= 0) { loadTrack(selectedIndex); playTrack(); }
    });
    if (nextBtn) nextBtn.addEventListener('click', () => { if (!tracks.length) return; currentTrack = (currentTrack + 1) % tracks.length; loadTrack(currentTrack); playTrack(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { if (!tracks.length) return; currentTrack = (currentTrack - 1 + tracks.length) % tracks.length; loadTrack(currentTrack); playTrack(); });
    if (audio) {
        audio.addEventListener('ended', () => nextBtn && nextBtn.click());
        audio.addEventListener('error', () => { if (statusEl) { statusEl.innerText = 'File audio eror'; statusEl.style.color = '#ff6b6b'; } });
    }
});
