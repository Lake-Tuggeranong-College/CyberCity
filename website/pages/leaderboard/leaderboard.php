<?php
// Buffer before any header() calls
ob_start();

$sec  = 30;
$page = $_SERVER['PHP_SELF'];
header("Refresh:$sec; url=$page");

require_once "../../includes/template.php";
/** @var PDO $conn */

if (!authorisedAccess(true, true, true)) {
    header("Location:../../index.php");
    exit;
}

// Fetch users (Enabled normal users), order by score desc
$query = "SELECT ID, Username, Score
          FROM Users
          WHERE Enabled = 1 AND AccessLevel = 1
          ORDER BY Score DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$userScore = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helpers
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function initials(string $name): string {
    $name = trim(preg_replace('/\s+/', ' ', $name));
    if ($name === '') return 'U';
    $parts = explode(' ', $name);
    $first = mb_substr($parts[0], 0, 1);
    $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}
function hslFromName(string $name): string {
    $hash = crc32($name);
    $hue  = $hash % 360;
    return "hsl($hue, 70%, 45%)";
}

$topScore = (int)($userScore[0]['Score'] ?? 0);
if ($topScore <= 0) $topScore = 1;

$topThree = array_slice($userScore, 0, 3);
$rest     = array_slice($userScore, 3, 7);
?>
    <style>
        /* =========================
           THEME TOKENS (inherit site)
           ========================= */
        :root{
            /* Page background stays whatever your site already sets */
            --lb-bg: var(--bs-body-bg, #f0f2f5);

            /* Light-mode text/muted pull from Bootstrap */
            --lb-text: var(--bs-body-color, #212529);
            --lb-muted: var(--bs-secondary-color, #6c757d);

            /* Panels/tiles */
            --lb-card-bg: var(--bs-card-bg, #ffffff);
            --lb-border: var(--bs-border-color, #dee2e6);

            /* Accents for bars (use site colours) */
            --lb-glow-1: var(--bs-primary, #0d6efd);
            --lb-glow-2: var(--bs-success, #198754);

            --lb-shadow: 0 18px 50px rgba(0,0,0,.08);
            --lb-card-radius: 18px;
        }

        /* STRONGER DARK MODE CONTRAST */
        [data-bs-theme="dark"] :root,
        body.bg-dark {
            /* Brighter body text and muted text for readability */
            --lb-text: #f1f5f9;     /* slate-100 */
            --lb-muted: #cbd5e1;    /* slate-300 */

            /* Darker card with a touch of alpha for depth, and clearer borders */
            --lb-card-bg: rgba(17, 24, 39, 0.92);   /* near #111827 */
            --lb-border: #3b4257;                   /* clearer border line */

            /* Shadows read better on dark */
            --lb-shadow: 0 18px 50px rgba(0,0,0,.45);
        }

        /* ===========
           CONTAINER
           =========== */
        .leaderboard-root{
            max-width: 1100px;
            margin: 24px auto 32px;
            padding: 0 12px;
            color: var(--lb-text);
            background: transparent; /* keep page bg from your site */
        }
        .lb-hero{
            margin-bottom: 18px;
            font-weight: 800;
            letter-spacing: .3px;
            display:flex; align-items:center; gap:10px;
            color: var(--lb-text);
        }
        .lb-hero .dot{
            width:10px;height:10px;border-radius:999px;background:var(--lb-glow-1);
            box-shadow: 0 0 8px var(--lb-glow-1);
        }

        /* ===========
           PANEL BASE
           =========== */
        .lb-panel{
            position:relative;
            border-radius: var(--lb-card-radius);
            border:1px solid var(--lb-border);
            background: var(--lb-card-bg);
            box-shadow: var(--lb-shadow);
            overflow:hidden;
            backdrop-filter: blur(6px);
            color: var(--lb-text);
        }

        /* =================
           TOP THREE PODIUM
           ================= */
        .podium-wrap{ padding: 18px 16px 22px; }
        .podium{
            display:grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap:16px;
            align-items:end;
        }
        .podium-card{
            position:relative; text-align:center;
            padding:16px 14px 18px; border-radius:16px;
            background: var(--lb-card-bg);
            border:1px solid var(--lb-border);
            overflow:hidden;
            color: var(--lb-text);
        }
        .podium-2{ min-height: 220px; }
        .podium-1{ min-height: 260px; transform: translateY(-14px); }
        .podium-3{ min-height: 200px; }

        /* GOLD / SILVER / BRONZE medals with readable numbers */
        .medal{
            position:absolute; top:10px; right:10px; width:38px; height:38px; border-radius:999px;
            display:grid; place-items:center; font-weight:900; font-size:.95rem;
            color:#111827; /* dark text on bright medal */
            box-shadow: 0 8px 16px rgba(0,0,0,.25);
        }
        .gold{
            background:
                    radial-gradient(circle at 30% 30%, #fff3a3, #f1c40f 70%),
                    linear-gradient(135deg, rgba(255,255,255,.7), transparent 35%);
        }
        .silver{
            background:
                    radial-gradient(circle at 30% 30%, #f7f8fa, #c7cbd1 70%),
                    linear-gradient(135deg, rgba(255,255,255,.7), transparent 35%);
        }
        .bronze{
            background:
                    radial-gradient(circle at 30% 30%, #ffdcb3, #c8892b 70%),
                    linear-gradient(135deg, rgba(255,255,255,.6), transparent 35%);
        }

        .avatar{
            width:84px; height:84px; border-radius:18px; margin: 24px auto 10px;
            display:grid; place-items:center; font-weight:900; font-size:1.15rem; color:#fff;
            box-shadow: inset 0 0 0 2px rgba(255,255,255,.25);
        }
        .name{
            font-weight:800; margin:6px 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
            color: var(--lb-text);
        }
        .score{ color: var(--lb-muted); font-weight:700; margin-bottom:10px; }

        .bar{
            height:12px; border-radius:999px; border:1px solid var(--lb-border);
            background: rgba(0,0,0,.06); overflow:hidden;
        }
        [data-bs-theme="dark"] .bar,
        body.bg-dark .bar{
            background: rgba(255,255,255,.08);
        }
        .bar > i{
            display:block; height:100%; width:0%;
            background: linear-gradient(90deg, var(--lb-glow-1), var(--lb-glow-2));
            transition: width .5s ease;
        }

        /* ======================
           RANKS 4–10 LIST STYLE
           ====================== */
        .list-wrap{ margin-top:18px; padding: 14px 12px 10px; }
        .list-row{
            display:grid; grid-template-columns: 52px 70px 1fr 130px;
            align-items:center; gap:14px;
            margin:10px 0; padding:10px 12px; border-radius:14px;
            border:1px solid var(--lb-border);
            background: var(--lb-card-bg);
            color: var(--lb-text);
        }
        .pos{ text-align:center; font-weight:900; font-size:1.1rem; color: var(--lb-text); }
        .avatar-sm{
            width:60px; height:60px; border-radius:14px; display:grid; place-items:center; color:#fff;
            font-weight:800; box-shadow: inset 0 0 0 2px rgba(255,255,255,.22);
        }
        .list-row .name{ color: var(--lb-text); }
        .mini{
            display:flex; align-items:center; gap:8px; justify-content:flex-end;
        }
        .mini > .mini-bar{
            flex:1 1 auto; height:8px; border-radius:999px; background: rgba(0,0,0,.06);
            overflow:hidden; border:1px solid var(--lb-border); max-width:90px;
        }
        [data-bs-theme="dark"] .mini > .mini-bar,
        body.bg-dark .mini > .mini-bar{
            background: rgba(255,255,255,.08);
        }
        .mini > .mini-bar > i{
            display:block; height:100%; background: linear-gradient(90deg, var(--lb-glow-1), var(--lb-glow-2));
            width:0%;
            transition: width .5s ease;
        }
        .mini .num{ font-weight:800; min-width:46px; text-align:right; color: var(--lb-text); }

        .section-title{
            margin: 2px 0 6px 6px; font-weight:800; color: var(--lb-muted); letter-spacing:.3px;
        }

        /* Responsive */
        @media (max-width: 720px){
            .podium{ grid-template-columns: 1fr; }
            .podium-1, .podium-2, .podium-3{ transform:none; min-height:auto; }
        }
    </style>

    <div class="leaderboard-root">
        <div class="lb-hero">
            <span class="dot" aria-hidden="true"></span>
            <h2 class="m-0">Leaderboard</h2>
            <div class="ms-auto" style="opacity:.85; font-size:.9rem; color: var(--lb-muted);">
                Auto-refreshes every <?= (int)$sec ?>s
            </div>
        </div>

        <?php if (!$userScore): ?>
            <div class="lb-panel" style="padding:18px; text-align:center;">
                <div class="section-title" style="margin-bottom:8px;">No players yet</div>
                <div style="color:var(--lb-muted)">Scores will appear as soon as players start earning points.</div>
            </div>
        <?php else: ?>

            <!-- Top 3 Podium -->
            <div class="lb-panel podium-wrap">
                <div class="podium">
                    <?php
                    for ($i=0; $i<3; $i++):
                        $u = $topThree[$i] ?? null;
                        $rankClass = ($i===0?'podium-1':($i===1?'podium-2':'podium-3'));
                        $medalClass = ($i===0?'gold':($i===1?'silver':'bronze'));
                        $ratio = $u ? min(100, max(0, round(($u['Score'] / $topScore) * 100))) : 0;
                        $bg = $u ? hslFromName($u['Username']) : 'hsl(210,20%,45%)';
                        $init = $u ? initials($u['Username']) : '–';
                        $name = $u ? e($u['Username']) : 'Awaiting Player';
                        $score = $u ? (int)$u['Score'] : 0;
                        ?>
                        <div class="podium-card <?= $rankClass ?>">
                            <div class="medal <?= $medalClass ?>" title="Rank <?= $i+1 ?>"><?= $i+1 ?></div>
                            <div class="avatar" style="background: <?= e($bg) ?>;"><?= $init ?></div>
                            <div class="name" title="<?= $name ?>"><?= $name ?></div>
                            <div class="score">Score: <?= $score ?></div>
                            <div class="bar" aria-hidden="true"><i style="width: <?= $ratio ?>%"></i></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Ranks 4–10 -->
            <?php if ($rest): ?>
                <div class="section-title">Ranks 4–10</div>
                <div class="lb-panel list-wrap">
                    <?php foreach ($rest as $idx => $u):
                        $pos   = $idx + 4;
                        $ratio = min(100, max(0, round(($u['Score'] / $topScore) * 100)));
                        $bg    = hslFromName($u['Username']);
                        $init  = initials($u['Username']);
                        ?>
                        <div class="list-row">
                            <div class="pos"><?= $pos ?></div>
                            <div class="avatar-sm" style="background: <?= e($bg) ?>;"><?= e($init) ?></div>
                            <div class="name" title="<?= e($u['Username']) ?>"><?= e($u['Username']) ?></div>
                            <div class="mini">
                                <div class="mini-bar" aria-hidden="true"><i style="width: <?= $ratio ?>%"></i></div>
                                <div class="num"><?= (int)$u['Score'] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

<?php
ob_end_flush();
