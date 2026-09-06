<?php
declare(strict_types=1);

/**
 * Prijava korisnika, uloge i kontrola pristupa.
 *
 * Lozinke se cuvaju iskljucivo kao hash (password_hash / PASSWORD_DEFAULT),
 * a provera ide preko password_verify. U bazi nema citljivih lozinki.
 */
final class Auth
{
    private const MAX_POKUSAJA = 5;
    private const PAUZA_SEK    = 300; // 5 minuta

    /** Pokretanje sesije sa bezbedno podesenim kolacicem. */
    public static function pokreniSesiju(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => BASE_URL,
            'httponly' => true,   // kolacic nije dostupan JavaScript-u
            'samesite' => 'Lax',  // ne salje se sa tudjih sajtova
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        session_name('FINISHLINE_SID');
        session_start();
    }

    public static function prijavljen(): bool
    {
        return isset($_SESSION['korisnik']['id']);
    }

    public static function korisnik(): ?array
    {
        return $_SESSION['korisnik'] ?? null;
    }

    public static function id(): int
    {
        return (int) ($_SESSION['korisnik']['id'] ?? 0);
    }

    public static function uloga(): string
    {
        return (string) ($_SESSION['korisnik']['uloga'] ?? 'gost');
    }

    public static function jeAdmin(): bool
    {
        return self::uloga() === 'admin';
    }

    /**
     * Pokusaj prijave.
     *
     * @return string|null Poruka o gresci, ili null ako je prijava uspela.
     */
    public static function prijavi(string $email, string $lozinka): ?string
    {
        if (self::blokiran()) {
            return 'Previse neuspesnih pokusaja. Pokusajte ponovo za nekoliko minuta.';
        }

        $model    = new Korisnik();
        $korisnik = $model->poEmailu($email);

        // Ista poruka i za nepostojeci nalog i za pogresnu lozinku,
        // da se napadacu ne otkrije koji nalozi postoje.
        if ($korisnik === null || !password_verify($lozinka, $korisnik['lozinka_hash'])) {
            self::zabeleziNeuspeh();
            return 'Pogresan email ili lozinka.';
        }

        if ((int) $korisnik['aktivan'] !== 1) {
            return 'Nalog je deaktiviran.';
        }

        // Ako se u medjuvremenu promenio podrazumevani algoritam - prehesuj
        if (password_needs_rehash($korisnik['lozinka_hash'], PASSWORD_DEFAULT)) {
            $model->postaviLozinku((int) $korisnik['id'], $lozinka);
        }

        // Nov ID sesije sprecava session fixation napad
        session_regenerate_id(true);

        $_SESSION['korisnik'] = [
            'id'    => (int) $korisnik['id'],
            'ime'   => $korisnik['ime'],
            'email' => $korisnik['email'],
            'uloga' => $korisnik['uloga'],
        ];
        unset($_SESSION['pokusaji'], $_SESSION['pokusaji_do']);

        $model->zabeleziPrijavu((int) $korisnik['id']);
        return null;
    }

    public static function odjavi(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }

    /** Prekida izvrsavanje ako korisnik nije prijavljen. */
    public static function zahtevajPrijavu(): void
    {
        if (!self::prijavljen()) {
            $_SESSION['posle_prijave'] = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: ' . url('prijava'));
            exit;
        }
    }

    /** Prekida izvrsavanje ako prijavljeni korisnik nije administrator. */
    public static function zahtevajAdmina(): void
    {
        self::zahtevajPrijavu();

        if (!self::jeAdmin()) {
            (new GreskaController())->zabranjeno();
            exit;
        }
    }

    // --- Ogranicavanje broja pokusaja prijave ---------------------------

    private static function blokiran(): bool
    {
        if (($_SESSION['pokusaji'] ?? 0) < self::MAX_POKUSAJA) {
            return false;
        }
        if (time() > ($_SESSION['pokusaji_do'] ?? 0)) {
            unset($_SESSION['pokusaji'], $_SESSION['pokusaji_do']);
            return false;
        }
        return true;
    }

    private static function zabeleziNeuspeh(): void
    {
        $_SESSION['pokusaji']    = ($_SESSION['pokusaji'] ?? 0) + 1;
        $_SESSION['pokusaji_do'] = time() + self::PAUZA_SEK;
    }
}
