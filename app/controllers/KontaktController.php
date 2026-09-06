<?php
declare(strict_types=1);

/**
 * Kontakt strana i prijem upita bez JavaScript-a.
 *
 * Ista forma se normalno salje Ajax-om na api/upiti. Ova metoda je
 * rezervni put, da forma radi i kada je JavaScript iskljucen.
 */
final class KontaktController extends Controller
{
    public function index(): void
    {
        $greske = $_SESSION['greske_upit'] ?? [];
        unset($_SESSION['greske_upit']);

        $this->prikaz('kontakt/index', [
            'naslovStrane' => 'Kontakt i procena - ' . APP_NAME,
            'opisStrane'   => 'Pozovite nas ili pošaljite upit. Odgovaramo u roku od 24 sata, izlazak na teren je besplatan.',
            'usluge'       => $this->model('Usluga')->aktivne(),
            'greske'       => $greske,
        ]);
    }

    public function posalji(): void
    {
        if (!$this->jePost()) {
            $this->preusmeri('kontakt');
        }

        $this->zahtevajCsrf();

        $podaci = [
            'ime'        => $this->unos('ime', ''),
            'email'      => $this->unos('email', ''),
            'telefon'    => $this->unos('telefon', ''),
            'usluga_id'  => $this->unos('usluga_id', ''),
            'kvadratura' => $this->unos('kvadratura', ''),
            'poruka'     => $this->unos('poruka', ''),
            'website'    => $this->unos('website', ''),
        ];

        $greske = Upit::proveri($podaci);

        if ($greske !== []) {
            $_SESSION['greske_upit'] = $greske;
            $_SESSION['stari_unos']  = $podaci;
            poruka('greska', 'Molimo ispravite označena polja.');
            $this->preusmeri('kontakt#forma');
        }

        $model = $this->model('Upit');

        // Zastita od zatrpavanja upitima sa iste adrese
        if ($model->skorasnjiSaIste((string) ($_SERVER['REMOTE_ADDR'] ?? '')) >= 5) {
            poruka('greska', 'Poslali ste previše upita. Pokušajte kasnije ili nas pozovite.');
            $this->preusmeri('kontakt#forma');
        }

        $model->napravi([
            'ime'        => $podaci['ime'],
            'email'      => $podaci['email'],
            'telefon'    => $podaci['telefon'],
            'usluga_id'  => $podaci['usluga_id'] !== '' ? (int) $podaci['usluga_id'] : null,
            'kvadratura' => $podaci['kvadratura'] !== '' ? (int) $podaci['kvadratura'] : null,
            'poruka'     => $podaci['poruka'],
            'procena'    => null,
        ]);

        unset($_SESSION['stari_unos']);
        poruka('uspeh', 'Hvala! Upit je primljen, javljamo se u roku od 24 sata.');
        $this->preusmeri('kontakt#forma');
    }
}
