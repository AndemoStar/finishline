<?php
declare(strict_types=1);

/**
 * Zajednicka osnova svih kontrolera.
 *
 * Kontroler prima zahtev, poziva model i bira prikaz. U kontroleru
 * nema SQL upita niti veceg dela HTML-a - to je posao modela i prikaza.
 */
abstract class Controller
{
    /**
     * Iscrtava prikaz unutar zadatog layout-a.
     *
     * @param string $prikaz npr. 'home/index'
     * @param array  $podaci promenljive dostupne u prikazu
     */
    protected function prikaz(string $prikaz, array $podaci = [], string $layout = 'main'): void
    {
        $fajl = APP_ROOT . '/app/views/' . $prikaz . '.php';

        if (!is_file($fajl)) {
            throw new RuntimeException('Prikaz nije pronadjen: ' . $prikaz);
        }

        extract($podaci, EXTR_SKIP);

        // Sadrzaj prikaza se hvata u promenljivu, pa ga layout ispisuje
        ob_start();
        require $fajl;
        $sadrzaj = ob_get_clean();

        require APP_ROOT . '/app/views/layouts/' . $layout . '.php';
    }

    /** Prikaz bez layout-a (za delove strane koji se dovlace Ajax-om). */
    protected function deo(string $prikaz, array $podaci = []): void
    {
        $fajl = APP_ROOT . '/app/views/' . $prikaz . '.php';

        if (is_file($fajl)) {
            extract($podaci, EXTR_SKIP);
            require $fajl;
        }
    }

    /** Odgovor u JSON formatu - koristi se u web servisima. */
    protected function json($podaci, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($podaci, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Preusmeravanje na drugu adresu unutar aplikacije. */
    protected function preusmeri(string $putanja = ''): void
    {
        header('Location: ' . url($putanja));
        exit;
    }

    /** Ocitava i cisti vrednost iz GET/POST zahteva. */
    protected function unos(string $polje, $podrazumevano = null)
    {
        $vrednost = $_POST[$polje] ?? $_GET[$polje] ?? $podrazumevano;
        return is_string($vrednost) ? trim($vrednost) : $vrednost;
    }

    protected function ceoBroj(string $polje, int $podrazumevano = 0): int
    {
        return (int) ($this->unos($polje, $podrazumevano));
    }

    protected function jePost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /** Da li je zahtev stigao preko Ajax-a. */
    protected function jeAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /**
     * Provera CSRF tokena. Kod obicne forme preusmerava,
     * kod Ajax poziva vraca JSON gresku.
     */
    protected function zahtevajCsrf(): void
    {
        if (Csrf::ispravan()) {
            return;
        }

        if ($this->jeAjax()) {
            $this->json(['uspeh' => false, 'poruka' => 'Bezbednosni token nije ispravan. Osvezite stranu.'], 403);
        }

        poruka('greska', 'Bezbednosni token nije ispravan. Pokusajte ponovo.');
        $this->preusmeri();
    }

    /** Kratak nacin da se dobije instanca modela. */
    protected function model(string $naziv): Model
    {
        return new $naziv();
    }
}
