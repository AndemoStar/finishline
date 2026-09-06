<?php
declare(strict_types=1);

/**
 * Zastita od CSRF napada (Cross-Site Request Forgery).
 *
 * Svaka forma i svaki Ajax poziv koji menja podatke nosi token iz sesije.
 * Zahtev bez ispravnog tokena se odbija, pa tudji sajt ne moze da natera
 * prijavljenog korisnika da posalje zahtev nasoj aplikaciji.
 */
final class Csrf
{
    private const KLJUC = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KLJUC])) {
            $_SESSION[self::KLJUC] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KLJUC];
    }

    /** Skriveno polje koje se ubacuje u svaku formu. */
    public static function polje(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    /** Provera tokena - iz POST polja ili iz zaglavlja (kod Ajax poziva). */
    public static function ispravan(?string $token = null): bool
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

        if (!is_string($token) || $token === '' || empty($_SESSION[self::KLJUC])) {
            return false;
        }

        // hash_equals poredi u konstantnom vremenu (bez curenja informacija)
        return hash_equals($_SESSION[self::KLJUC], $token);
    }
}
