<?php
declare(strict_types=1);

/**
 * Jezgro aplikacije - ucitava klase, definise rute i pokrece kontroler.
 */
final class Aplikacija
{
    private Router $ruter;

    public function __construct()
    {
        $this->ucitajKlase();
        $this->podesiGreske();

        Auth::pokreniSesiju();

        $this->ruter = new Router();
        $this->definisiRute();
    }

    /** Automatsko ucitavanje klasa iz core/, models/ i controllers/. */
    private function ucitajKlase(): void
    {
        spl_autoload_register(static function (string $klasa): void {
            $folderi = ['core', 'models', 'controllers'];

            foreach ($folderi as $folder) {
                $fajl = APP_ROOT . '/app/' . $folder . '/' . $klasa . '.php';

                if (is_file($fajl)) {
                    require_once $fajl;
                    return;
                }
            }
        });

        require_once APP_ROOT . '/app/core/pomocne.php';
    }

    private function podesiGreske(): void
    {
        if (DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }
    }

    /**
     * Sve adrese aplikacije na jednom mestu.
     */
    private function definisiRute(): void
    {
        $r = $this->ruter;

        // --- Javne stranice ---------------------------------------------
        $r->dodaj('',              'HomeController',    'index');
        $r->dodaj('usluge',        'UslugeController',  'index');
        $r->dodaj('usluge/{slug}', 'UslugeController',  'prikazi');
        $r->dodaj('radovi',        'RadoviController',  'index');
        $r->dodaj('radovi/{id}',   'RadoviController',  'prikazi');
        $r->dodaj('o-nama',        'StraneController',  'oNama');
        $r->dodaj('kontakt',       'KontaktController', 'index');
        $r->dodaj('kontakt/posalji', 'KontaktController', 'posalji');

        // --- Prijava / odjava -------------------------------------------
        $r->dodaj('prijava', 'AuthController', 'prijava');
        $r->dodaj('odjava',  'AuthController', 'odjava');

        // --- Administracija (samo za prijavljenog admina) ---------------
        $r->dodaj('admin',                 'AdminController', 'pocetna');
        $r->dodaj('admin/upiti',           'AdminController', 'upiti');
        $r->dodaj('admin/radovi',          'AdminController', 'radovi');
        $r->dodaj('admin/radovi/nov',      'AdminController', 'novRad');
        $r->dodaj('admin/radovi/izmeni/{id}', 'AdminController', 'izmeniRad');
        $r->dodaj('admin/radovi/sacuvaj',  'AdminController', 'sacuvajRad');
        $r->dodaj('admin/usluge',          'AdminController', 'usluge');
        $r->dodaj('admin/usluge/sacuvaj',  'AdminController', 'sacuvajUslugu');
        $r->dodaj('admin/utisci',          'AdminController', 'utisci');

        // --- Web servisi (REST, odgovaraju u JSON formatu) --------------
        $r->dodaj('api/usluge',            'ApiController', 'usluge');
        $r->dodaj('api/radovi',            'ApiController', 'radovi');
        $r->dodaj('api/radovi/{id}',       'ApiController', 'rad');
        $r->dodaj('api/utisci',            'ApiController', 'utisci');
        $r->dodaj('api/statistika',        'ApiController', 'statistika');
        $r->dodaj('api/upiti',             'ApiController', 'upiti');
        $r->dodaj('api/upiti/{id}/status', 'ApiController', 'statusUpita');
        $r->dodaj('api/upload',            'ApiController', 'upload');
        $r->dodaj('api/slike/{id}',        'ApiController', 'slika');
        $r->dodaj('api/procena',           'ApiController', 'procena');
    }

    /** Pronalazi rutu i poziva odgovarajucu metodu kontrolera. */
    public function pokreni(): void
    {
        $url = $this->trenutnaAdresa();
        $GLOBALS['TRENUTNA_RUTA'] = $url;

        $ruta = $this->ruter->pronadji($url);

        if ($ruta === null) {
            (new GreskaController())->nepostojeca();
            return;
        }

        $klasa  = $ruta['kontroler'];
        $metoda = $ruta['metoda'];

        if (!class_exists($klasa) || !method_exists($klasa, $metoda)) {
            (new GreskaController())->nepostojeca();
            return;
        }

        try {
            $kontroler = new $klasa();
            $kontroler->$metoda(...$ruta['parametri']);
        } catch (Throwable $e) {
            $this->obradiIzuzetak($e);
        }
    }

    /** Cita adresu iz .htaccess parametra ili iz REQUEST_URI. */
    private function trenutnaAdresa(): string
    {
        if (isset($_GET['url'])) {
            return trim((string) $_GET['url'], '/');
        }

        // Rezervni nacin, ako mod_rewrite nije ukljucen
        $putanja = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $osnova  = rtrim(BASE_URL, '/');

        if ($osnova !== '' && strpos($putanja, $osnova) === 0) {
            $putanja = substr($putanja, strlen($osnova));
        }

        return trim(rawurldecode($putanja), '/');
    }

    private function obradiIzuzetak(Throwable $e): void
    {
        error_log('[Finish Line] ' . $e->getMessage() . ' u ' . $e->getFile() . ':' . $e->getLine());

        if (DEBUG) {
            http_response_code(500);
            echo '<pre style="padding:24px;font:14px/1.6 monospace;background:#1a1a1a;color:#ff8a8a">';
            echo 'GRESKA: ' . e($e->getMessage()) . "\n\n";
            echo e($e->getFile()) . ':' . $e->getLine() . "\n\n";
            echo e($e->getTraceAsString());
            echo '</pre>';
            return;
        }

        (new GreskaController())->greskaServera();
    }
}
