<?php
/**
 * Osnovna konfiguracija aplikacije.
 *
 * Podaci koji se razlikuju izmedju lokalnog racunara i hostinga
 * (pre svega lozinka baze) upisuju se u config.local.php, koji nije
 * u repozitorijumu. Taj fajl se ucitava PRE podrazumevanih vrednosti,
 * jer u PHP-u vazi prvi define() - kasniji ga ne menja.
 */
declare(strict_types=1);

// --- Lokalna podesavanja (ako postoje, imaju prednost) --------------------
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// --- Podaci o firmi -------------------------------------------------------
define('APP_NAME',    'Finish Line');
define('APP_SLOGAN',  'Zavrsni gradjevinski radovi');
define('FIRMA_TEL',   '+381 60 33 44 996');
define('FIRMA_MAIL',  'info@finishline.rs');
define('FIRMA_ADRESA','Stanoja Glavasa 35, Velika Plana');

// --- Baza podataka --------------------------------------------------------
// Podrazumevane vrednosti su za XAMPP. Na hostingu ih prepisuje
// config.local.php, pa lozinka nikada ne ulazi u repozitorijum.
if (!defined('DB_HOST')) { define('DB_HOST', 'localhost'); }
if (!defined('DB_NAME')) { define('DB_NAME', 'finishline'); }
if (!defined('DB_USER')) { define('DB_USER', 'root'); }
if (!defined('DB_PASS')) { define('DB_PASS', ''); }
if (!defined('DB_CHARSET')) { define('DB_CHARSET', 'utf8mb4'); }

// --- Putanje --------------------------------------------------------------
define('APP_ROOT',   dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/assets/uploads');

// --- Otpremanje slika -----------------------------------------------------
define('UPLOAD_MAX_BYTES', 3 * 1024 * 1024); // 3 MB

// --- Rezim rada -----------------------------------------------------------
// Na hostingu se u config.local.php postavlja na false,
// da se poruke o greskama ne prikazuju posetiocu.
if (!defined('DEBUG')) { define('DEBUG', true); }

/**
 * Osnovna adresa aplikacije, racuna se sama iz putanje do index.php.
 * Zahvaljujuci ovome projekat radi i u htdocs/finishline i u htdocs/sup25/ak.
 */
$dir = strtr(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), DIRECTORY_SEPARATOR, '/');
$dir = rtrim($dir, '/');
define('BASE_URL', $dir . '/');
