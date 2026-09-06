<?php
/**
 * Osnovna konfiguracija aplikacije.
 *
 * Vrednosti koje se razlikuju izmedju lokalnog racunara i hostinga
 * se prepisuju u config.local.php, koji nije u repozitorijumu.
 */
declare(strict_types=1);

// --- Podaci o firmi -------------------------------------------------------
define('APP_NAME',    'Finish Line');
define('APP_SLOGAN',  'Zavrsni gradjevinski radovi');
define('FIRMA_TEL',   '+381 60 33 44 996');
define('FIRMA_MAIL',  'info@finishline.rs');
define('FIRMA_ADRESA','Stanoja Glavasa 35, Velika Plana');


// --- Baza podataka --------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'finishline');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Putanje --------------------------------------------------------------
define('APP_ROOT',   dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/assets/uploads');

// --- Otpremanje slika -----------------------------------------------------
define('UPLOAD_MAX_BYTES', 3 * 1024 * 1024); // 3 MB

// --- Rezim rada -----------------------------------------------------------
// Na hostingu postaviti na false da se greske ne prikazuju posetiocu.
define('DEBUG', true);

// Lokalna podesavanja (lozinka baze na hostingu i slicno)
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

/**
 * Osnovna adresa aplikacije, racuna se sama iz putanje do index.php.
 * Zahvaljujuci ovome projekat radi i u htdocs/finishline i u htdocs/sup25/ak.
 */
$dir = strtr(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), DIRECTORY_SEPARATOR, '/');
$dir = rtrim($dir, '/');
define('BASE_URL', $dir . '/');
