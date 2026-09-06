<?php
declare(strict_types=1);

/**
 * Jedna PDO veza ka bazi za ceo zahtev (singleton).
 *
 * Emulacija pripremljenih upita je iskljucena, pa upite priprema sam
 * MySQL server - to je kljucno za zastitu od SQL injection napada.
 */
final class Database
{
    private static ?PDO $veza = null;

    private function __construct() {}

    public static function veza(): PDO
    {
        if (self::$veza !== null) {
            return self::$veza;
        }

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

        try {
            self::$veza = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            if (DEBUG) {
                die('<h1>Greska pri povezivanju sa bazom</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>'
                  . '<p>Proverite da li je MySQL pokrenut i da li je baza <b>' . DB_NAME . '</b> uvezena.</p>');
            }
            http_response_code(500);
            die('Trenutno nije moguce pristupiti bazi podataka.');
        }

        return self::$veza;
    }
}
