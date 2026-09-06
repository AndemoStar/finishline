<?php
declare(strict_types=1);

/**
 * Prijava i odjava korisnika.
 */
final class AuthController extends Controller
{
    public function prijava(): void
    {
        if (Auth::prijavljen()) {
            $this->preusmeri('admin');
        }

        $greska = null;
        $email  = '';

        if ($this->jePost()) {
            $this->zahtevajCsrf();

            $email   = (string) $this->unos('email', '');
            $lozinka = (string) ($_POST['lozinka'] ?? '');

            if ($email === '' || $lozinka === '') {
                $greska = 'Unesite email i lozinku.';
            } else {
                $greska = Auth::prijavi($email, $lozinka);

                if ($greska === null) {
                    // Povratak na stranu sa koje je korisnik poslat na prijavu
                    $nazad = $_SESSION['posle_prijave'] ?? '';
                    unset($_SESSION['posle_prijave']);

                    poruka('uspeh', 'Dobrodošli, ' . (Auth::korisnik()['ime'] ?? '') . '.');

                    if (is_string($nazad) && $nazad !== '' && strpos($nazad, '//') === false) {
                        header('Location: ' . $nazad);
                        exit;
                    }

                    $this->preusmeri('admin');
                }
            }
        }

        $this->prikaz('auth/prijava', [
            'naslovStrane' => 'Prijava - ' . APP_NAME,
            'greska'       => $greska,
            'email'        => $email,
        ], 'prazan');
    }

    public function odjava(): void
    {
        // Odjava se prihvata samo kao POST sa ispravnim tokenom,
        // da niko ne moze da odjavi korisnika obicnim linkom.
        if ($this->jePost()) {
            $this->zahtevajCsrf();
        }

        Auth::odjavi();
        Auth::pokreniSesiju();

        poruka('uspeh', 'Odjavljeni ste.');
        $this->preusmeri('prijava');
    }
}
