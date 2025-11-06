<?php include "includes/template.php"; /** @var $conn */ ?>
<title>Cyber City</title>

<style>
    /* Tokens that follow your current theme (dark/light) */
    :root{
        --cc-panel-bg: rgba(255,255,255,0.06);
        --cc-panel-border: rgba(255,255,255,0.14);
        --cc-text: var(--bs-body-color, #e6e9ef);
        --cc-muted: #9aa3b2;
        --cc-accent-1: #7dd3fc; /* cyan */
        --cc-accent-2: #a78bfa; /* violet */
        --cc-radius: 18px;
        --cc-shadow: 0 18px 50px rgba(0,0,0,.45);
    }
    [data-bs-theme="light"] :root{
        --cc-panel-bg: rgba(15,20,34,0.05);
        --cc-panel-border: rgba(15,20,34,0.10);
        --cc-text: var(--bs-body-color, #0f172a);
        --cc-muted: #6b7280;
        --cc-shadow: 0 16px 40px rgba(5,10,20,.08);
    }
    body.bg-dark{
        --cc-panel-bg: rgba(255,255,255,0.06);
        --cc-panel-border: rgba(255,255,255,0.14);
        --cc-text: #e6e9ef;
        --cc-muted: #9aa3b2;
        --cc-shadow: 0 18px 50px rgba(0,0,0,.45);
    }

    /* Layout */
    .cc-wrap{ max-width: 1200px; margin: 24px auto 64px; padding: 0 16px; color: var(--cc-text); }

    /* Hero */
    .cc-hero{
        position: relative;
        border-radius: calc(var(--cc-radius) + 6px);
        padding: 42px 28px;
        border: 1px solid var(--cc-panel-border);
        background:
                radial-gradient(1000px 600px at 10% -10%, rgba(167,139,250,.10), transparent 60%),
                radial-gradient(900px 500px at 90% 0%, rgba(125,211,252,.10), transparent 60%),
                linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
        box-shadow: var(--cc-shadow);
        overflow: hidden;
    }
    .cc-hero::after{
        content:""; position:absolute; inset:-1px; pointer-events:none; opacity:.18;
        background: linear-gradient(135deg, rgba(125,211,252,.35), rgba(167,139,250,.35));
        filter: blur(30px);
    }
    .cc-hero h1{ font-weight: 800; letter-spacing: .3px; margin-bottom: 6px; }
    .cc-hero p{ margin: 0; color: var(--cc-muted); font-weight: 600; }

    /* Sections (cards) */
    .cc-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:18px; margin-top:20px; }
    @media (max-width: 900px){ .cc-grid{ grid-template-columns: 1fr; } }

    .cc-card{
        position: relative;
        border-radius: var(--cc-radius);
        border: 1px solid var(--cc-panel-border);
        background: var(--cc-panel-bg);
        padding: 22px 22px 18px;
        box-shadow: var(--cc-shadow);
        overflow: hidden;
    }
    .cc-card h2{ font-weight: 800; margin-bottom: 10px; }
    .cc-card p{ margin: 0; color: var(--cc-text); }
    .cc-badge{
        display:inline-flex; align-items:center; gap:8px;
        padding:6px 10px; border-radius: 999px;
        font-size:.85rem; font-weight:800; letter-spacing:.2px;
        border:1px solid var(--cc-panel-border);
        background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
        color: var(--cc-muted);
        margin-bottom: 10px;
    }
    .cc-dot{
        width:10px; height:10px; border-radius:999px;
        background: var(--cc-accent-1); box-shadow: 0 0 10px var(--cc-accent-1);
    }
    .cc-divider{
        height:1px; width:100%; margin:12px 0 14px;
        background: linear-gradient(90deg, transparent, var(--cc-panel-border), transparent);
    }

    /* ========= Background music controller ========= */
    .bgm{
        position: fixed; right: 18px; bottom: 18px; z-index: 1000;
        display: flex; align-items: center; gap: 10px;
        border-radius: 14px;
        border: 1px solid var(--cc-panel-border);
        background: var(--cc-panel-bg);
        box-shadow: var(--cc-shadow);
        padding: 10px 12px;
        backdrop-filter: blur(8px);
    }
    .bgm .btn{
        display:inline-flex; align-items:center; justify-content:center;
        width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--cc-panel-border);
        background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
        color: var(--cc-text); cursor: pointer; user-select:none; font-weight: 800;
    }
    .bgm .btn:active{ transform: translateY(1px); }
    .bgm .status{
        font-size:.85rem; color: var(--cc-muted); min-width: 90px;
    }
    .bgm input[type="range"]{
        width: 120px; height: 6px; border-radius: 999px; outline:none;
        background: linear-gradient(90deg, var(--cc-accent-1), var(--cc-accent-2));
        appearance: none; -webkit-appearance: none; border: none;
    }
    .bgm input[type="range"]::-webkit-slider-thumb{
        -webkit-appearance: none; appearance: none; width:14px; height:14px; border-radius:50%;
        background: #fff; border: 2px solid rgba(0,0,0,.25);
    }
    @media (max-width: 560px){
        .bgm .status{ display:none; }
        .bgm input[type="range"]{ width: 90px; }
    }
</style>

<main class="cc-wrap">

    <!-- Hero -->
    <section class="cc-hero">
        <h1>Rebels, we need your help</h1>
        <p>
            <?php if (isset($_SESSION["username"])): ?>
                You're logged in — you may now contribute to the cause.
            <?php else: ?>
                Please log in or register to gain access to the cause.
            <?php endif; ?>
        </p>
    </section>

    <!-- Content Cards -->
    <section class="cc-grid">
        <article class="cc-card">
            <span class="cc-badge"><span class="cc-dot" aria-hidden="true"></span> Beginnings</span>
            <h2>Beginnings</h2>
            <div class="cc-divider"></div>
            <p>
                In 1850 a rural town was created, referred to as Latafa. This town was a logging town bringing in great riches
                for those who controlled it. During its earlier years, the town was a hot spot for illegal testing as it was
                far from any other towns. During the Red Tuesday bushfires in 1898, the town was consumed by a blazing inferno.
                Later rebuilt, it became isolated and was erased from modern maps. Its location remains unknown and lost to time.
            </p>
        </article>

        <article class="cc-card">
            <span class="cc-badge"><span class="cc-dot" aria-hidden="true" style="background:var(--cc-accent-2); box-shadow:0 0 10px var(--cc-accent-2)"></span> Currently</span>
            <h2>Currently</h2>
            <div class="cc-divider"></div>
            <p>
                Oak-Crack is the remains of the town, the forest creating a natural barrier for which the TBW can hide.
                We at the LTC have found that the TBW is currently cultivating a super-virus in a French Bio-Lab
                by the name of Lab 404 deep underground.
            </p>
        </article>
    </section>

</main>

<!-- ===== Background Music (user-controlled) ===== -->
<audio id="bgmAudio" preload="auto" loop playsinline autoplay>
    <source src="<?= rtrim(BASE_URL, '/'); ?>/assets/Audio/cyber-ambience.mp3" type="audio/mpeg">
    <source src="<?= rtrim(BASE_URL, '/'); ?>/assets/Audio/cyber-ambience.ogg" type="audio/ogg">
</audio>


<div class="bgm" role="group" aria-label="Background music controls">
    <button class="btn" id="bgmToggle" aria-pressed="false" title="Play/Pause">▶</button>
    <div class="status" id="bgmStatus">Muted</div>
    <input type="range" id="bgmVol" min="0" max="1" step="0.01" value="0.4" aria-label="Volume">
</div>

<script>
    (function(){
        const audio   = document.getElementById('bgmAudio');
        const toggle  = document.getElementById('bgmToggle');
        const status  = document.getElementById('bgmStatus');
        const vol     = document.getElementById('bgmVol');

        const DEFAULT_VOL = 0.15; // quieter by default

        // Restore saved volume (or use quiet default)
        const savedVol = parseFloat(localStorage.getItem('bgmVolume') || DEFAULT_VOL);
        const clamped  = isNaN(savedVol) ? DEFAULT_VOL : Math.max(0, Math.min(1, savedVol));
        vol.value = clamped;
        audio.volume = clamped;

        // Helper: fade to target volume
        function fadeTo(target, ms = 800){
            const start = audio.volume;
            const delta = target - start;
            if (Math.abs(delta) < 0.001) return;
            const t0 = performance.now();
            function step(t){
                const k = Math.min(1, (t - t0) / ms);
                audio.volume = start + delta * k;
                if (k < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        // Try autoplay quietly
        (async () => {
            try {
                audio.muted = false;
                await audio.play();
                // Success: show pause icon and status
                toggle.textContent = '❚❚';
                toggle.setAttribute('aria-pressed', 'true');
                status.textContent = 'Playing';
                localStorage.setItem('bgmEnabled', 'true');
                // Ensure we’re quietly at the chosen level
                fadeTo(clamped, 500);
            } catch {
                // Autoplay with sound blocked: start muted so music is "running"
                audio.muted = true;
                try { await audio.play(); } catch {}
                toggle.textContent = '▶';
                toggle.setAttribute('aria-pressed', 'false');
                status.textContent = 'Muted — click Play';
                localStorage.setItem('bgmEnabled', 'false');
            }
        })();

        // Button toggles play/pause + mute state
        toggle.addEventListener('click', async () => {
            if (audio.paused) {
                // unmute and play
                audio.muted = false;
                try {
                    await audio.play();
                    toggle.textContent = '❚❚';
                    toggle.setAttribute('aria-pressed', 'true');
                    status.textContent = 'Playing';
                    localStorage.setItem('bgmEnabled', 'true');
                    fadeTo(parseFloat(vol.value || DEFAULT_VOL), 300);
                } catch {
                    status.textContent = 'Click again to allow';
                }
            } else {
                audio.pause();
                toggle.textContent = '▶';
                toggle.setAttribute('aria-pressed', 'false');
                status.textContent = 'Paused';
                localStorage.setItem('bgmEnabled', 'false');
            }
        });

        // Volume slider
        vol.addEventListener('input', () => {
            const v = Math.max(0, Math.min(1, parseFloat(vol.value) || 0));
            audio.volume = v;
            localStorage.setItem('bgmVolume', v);
        });
    })();
</script>
