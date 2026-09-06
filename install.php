<?php
/**
 * Instalacija baze podataka za lokalno testiranje (XAMPP).
 *
 * Otvoriti u pregledacu: http://localhost/finishline/install.php
 * Skripta pravi bazu, tabele i demo podatke iz fajla sql/finishline.sql.
 *
 * PAZNJA: postojeće tabele se brišu i prave iznova.
 * Posle uspešne instalacije ovaj fajl obrisati.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

$potvrdjeno = isset($_GET['potvrda']) && $_GET['potvrda'] === 'da';
$poruke     = [];
$greska     = null;

/**
 * Deli SQL fajl na pojedinacne naredbe.
 * Vodi racuna o navodnicima i komentarima, da se ne prelomi na
 * tacki-zarezu koja se nalazi unutar teksta.
 */
function podeliSql(string $sql): array
{
    $naredbe  = [];
    $trenutna = '';
    $duzina   = strlen($sql);

    $uNavodniku = false;
    $znakNav    = '';
    $uKomentaru = false;

    for ($i = 0; $i < $duzina; $i++) {
        $z = $sql[$i];
        $s = $i + 1 < $duzina ? $sql[$i + 1] : '';

        // Komentar do kraja reda
        if (!$uNavodniku && !$uKomentaru && (($z === '-' && $s === '-') || $z === '#')) {
            $uKomentaru = true;
        }

        if ($uKomentaru) {
            if ($z === "\n") {
                $uKomentaru = false;
                $trenutna  .= $z;
            }
            continue;
        }

        // Ulazak i izlazak iz navodnika
        if (!$uNavodniku && ($z === "'" || $z === '"' || $z === '`')) {
            $uNavodniku = true;
            $znakNav    = $z;
        } elseif ($uNavodniku && $z === $znakNav) {
            // udvojeni navodnik unutar teksta
            if ($s === $znakNav) {
                $trenutna .= $z . $s;
                $i++;
                continue;
            }
            $uNavodniku = false;
        }

        // Kraj naredbe
        if (!$uNavodniku && $z === ';') {
            $naredba = trim($trenutna);
            if ($naredba !== '') {
                $naredbe[] = $naredba;
            }
            $trenutna = '';
            continue;
        }

        $trenutna .= $z;
    }

    $poslednja = trim($trenutna);
    if ($poslednja !== '') {
        $naredbe[] = $poslednja;
    }

    return $naredbe;
}

if ($potvrdjeno) {
    try {
        $sqlFajl = __DIR__ . '/sql/finishline.sql';

        if (!is_file($sqlFajl)) {
            throw new RuntimeException('Nije pronadjen fajl sql/finishline.sql');
        }

        // Veza bez izabrane baze - bazu pravi sam SQL fajl
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $naredbe = podeliSql((string) file_get_contents($sqlFajl));
        $izvrsio = 0;

        foreach ($naredbe as $naredba) {
            $pdo->exec($naredba);
            $izvrsio++;
        }

        $poruke[] = 'Izvršeno SQL naredbi: ' . $izvrsio;

        // Provera rezultata
        $pdo->exec('USE `' . DB_NAME . '`');

        foreach (['korisnici', 'usluge', 'radovi', 'utisci', 'upiti'] as $tabela) {
            $broj = (int) $pdo->query('SELECT COUNT(*) FROM `' . $tabela . '`')->fetchColumn();
            $poruke[] = 'Tabela ' . $tabela . ': ' . $broj . ' zapisa';
        }

        // Provera da li lozinka demo naloga radi
        $hash = $pdo->query("SELECT lozinka_hash FROM korisnici WHERE email = 'admin@finishline.rs'")->fetchColumn();

        if (!is_string($hash) || !password_verify('Demo1234', $hash)) {
            throw new RuntimeException('Hash lozinke demo naloga nije ispravan.');
        }

        $poruke[] = 'Provera lozinke demo naloga: uspešno';

        // Provera da li je folder za slike upisiv
        if (!is_dir(UPLOAD_DIR)) {
            @mkdir(UPLOAD_DIR, 0775, true);
        }

        $poruke[] = is_writable(UPLOAD_DIR)
            ? 'Folder assets/uploads je upisiv'
            : 'UPOZORENJE: folder assets/uploads nije upisiv - otpremanje slika neće raditi';

    } catch (Throwable $e) {
        $greska = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalacija - Finish Line</title>
    <style>
        body { font: 15px/1.7 system-ui, 'Segoe UI', sans-serif; background: #0e1116; color: #e8eaed; margin: 0; padding: 40px 20px; }
        .kutija { max-width: 680px; margin: 0 auto; background: #171b23; border-radius: 10px; padding: 34px; border-top: 4px solid #f0a02a; }
        h1 { font-size: 1.5rem; margin: 0 0 6px; }
        .znak { display: inline-grid; place-items: center; width: 46px; height: 46px; background: #f0a02a; color: #0e1116; border-radius: 6px; font-weight: 800; margin-bottom: 16px; }
        p { color: #a9b1bd; }
        ul { padding-left: 20px; }
        li { margin-bottom: 5px; color: #c7cdd6; }
        .ok { background: #10291d; border-left: 4px solid #1e8a5a; padding: 14px 18px; border-radius: 6px; margin: 18px 0; }
        .lose { background: #2c1512; border-left: 4px solid #c0392b; padding: 14px 18px; border-radius: 6px; margin: 18px 0; color: #ffb3ab; }
        .upozorenje { background: #2b2312; border-left: 4px solid #f0a02a; padding: 14px 18px; border-radius: 6px; margin: 18px 0; }
        a.dugme { display: inline-block; background: #f0a02a; color: #0e1116; padding: 13px 26px; border-radius: 5px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        a.dugme:hover { background: #d1861a; }
        a.tiho { color: #a9b1bd; margin-left: 14px; }
        code { background: #0e1116; padding: 2px 7px; border-radius: 3px; color: #f0a02a; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 7px 0; border-bottom: 1px solid #242a33; }
    </style>
</head>
<body>
<div class="kutija">
    <span class="znak">FL</span>
    <h1>Instalacija baze — Finish Line</h1>

    <?php if ($greska !== null): ?>
        <div class="lose">
            <strong>Greška:</strong><br>
            <?= htmlspecialchars($greska, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <p>Proverite da li je MySQL pokrenut u XAMPP kontrolnoj tabli i da li su podaci u <code>config/config.php</code> tačni.</p>
        <a class="dugme" href="install.php?potvrda=da">Pokušaj ponovo</a>

    <?php elseif ($potvrdjeno): ?>
        <div class="ok">
            <strong>Baza je uspešno napravljena.</strong>
            <ul>
                <?php foreach ($poruke as $p): ?>
                    <li><?= htmlspecialchars($p, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <p><strong>Nalozi za prijavu:</strong></p>
        <table>
            <tr><td>Administrator</td><td><code>admin@finishline.rs</code></td><td><code>Demo1234</code></td></tr>
            <tr><td>Urednik</td><td><code>urednik@finishline.rs</code></td><td><code>Demo1234</code></td></tr>
        </table>

        <div class="upozorenje" style="margin-top:22px">
            Obrišite fajl <code>install.php</code> pre postavljanja na hosting.
        </div>

        <a class="dugme" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">Otvori sajt</a>
        <a class="tiho" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>prijava">Prijava na administraciju</a>

    <?php else: ?>
        <p>
            Ova skripta pravi bazu <code><?= htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') ?></code>,
            sve tabele i demo podatke.
        </p>

        <div class="upozorenje">
            <strong>Pažnja:</strong> ako baza već postoji, njene tabele će biti obrisane i napravljene iznova.
            Svi podaci u njima biće izgubljeni.
        </div>

        <p>Podaci za povezivanje (iz <code>config/config.php</code>):</p>
        <table>
            <tr><td>Server</td><td><code><?= htmlspecialchars(DB_HOST, ENT_QUOTES, 'UTF-8') ?></code></td></tr>
            <tr><td>Baza</td><td><code><?= htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') ?></code></td></tr>
            <tr><td>Korisnik</td><td><code><?= htmlspecialchars(DB_USER, ENT_QUOTES, 'UTF-8') ?></code></td></tr>
        </table>

        <a class="dugme" href="install.php?potvrda=da">Napravi bazu</a>
    <?php endif; ?>
</div>
</body>
</html>
