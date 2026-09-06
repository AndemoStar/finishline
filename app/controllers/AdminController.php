<?php
declare(strict_types=1);

/**
 * Administracija sajta.
 *
 * Pristup se proverava na serveru, u konstruktoru, pre svake metode.
 * Sakrivanje linkova u prikazu nije zastita - zato je provera ovde.
 */
final class AdminController extends Controller
{
    public function __construct()
    {
        Auth::zahtevajAdmina();
    }

    /** Pocetna strana administracije sa pregledom brojeva. */
    public function pocetna(): void
    {
        $upiti  = $this->model('Upit');
        $radovi = $this->model('Rad');

        $this->prikaz('admin/pocetna', [
            'naslovStrane' => 'Administracija - ' . APP_NAME,
            'brojNovih'    => $upiti->brojPoStatusu('nov'),
            'brojUObradi'  => $upiti->brojPoStatusu('u_obradi'),
            'brojZavrsen'  => $upiti->brojPoStatusu('zavrsen'),
            'brojRadova'   => $radovi->prebroj(),
            'brojUsluga'   => $this->model('Usluga')->prebrojAktivne(),
            'poDanima'     => $upiti->poDanima(14),
            'poslednji'    => array_slice($upiti->svi(), 0, 6),
        ], 'admin');
    }

    /** Lista upita - tabela se osvezava Ajax-om preko api/upiti. */
    public function upiti(): void
    {
        $model  = $this->model('Upit');
        $status = (string) $this->unos('status', '');
        $status = in_array($status, Upit::STATUSI, true) ? $status : null;

        $this->prikaz('admin/upiti', [
            'naslovStrane' => 'Upiti - administracija',
            'upiti'        => $model->svi($status),
            'izabrani'     => $status,
            'brojevi'      => [
                'nov'      => $model->brojPoStatusu('nov'),
                'u_obradi' => $model->brojPoStatusu('u_obradi'),
                'zavrsen'  => $model->brojPoStatusu('zavrsen'),
                'odbijen'  => $model->brojPoStatusu('odbijen'),
            ],
        ], 'admin');
    }

    // --- Radovi ---------------------------------------------------------

    public function radovi(): void
    {
        $this->prikaz('admin/radovi', [
            'naslovStrane' => 'Radovi - administracija',
            'radovi'       => $this->model('Rad')->sviZaAdmina(),
        ], 'admin');
    }

    public function novRad(): void
    {
        $this->formaRada(null);
    }

    public function izmeniRad(string $id): void
    {
        $rad = $this->model('Rad')->nadji((int) $id);

        if ($rad === null) {
            poruka('greska', 'Traženi rad ne postoji.');
            $this->preusmeri('admin/radovi');
        }

        $this->formaRada($rad);
    }

    private function formaRada(?array $rad): void
    {
        $greske = $_SESSION['greske_rad'] ?? [];
        unset($_SESSION['greske_rad']);

        $this->prikaz('admin/rad-forma', [
            'naslovStrane' => ($rad === null ? 'Nov rad' : 'Izmena rada') . ' - administracija',
            'rad'          => $rad,
            'usluge'       => $this->model('Usluga')->sve(),
            'slike'        => $rad !== null ? $this->model('Rad')->slike((int) $rad['id']) : [],
            'greske'       => $greske,
        ], 'admin');
    }

    public function sacuvajRad(): void
    {
        if (!$this->jePost()) {
            $this->preusmeri('admin/radovi');
        }

        $this->zahtevajCsrf();

        $id     = $this->ceoBroj('id', 0);
        $naziv  = (string) $this->unos('naziv', '');
        $opis   = (string) $this->unos('opis', '');
        $greske = [];

        if (mb_strlen($naziv, 'UTF-8') < 3) {
            $greske['naziv'] = 'Naziv rada mora imati bar 3 znaka.';
        }

        if (mb_strlen($opis, 'UTF-8') < 10) {
            $greske['opis'] = 'Opišite rad sa bar 10 znakova.';
        }

        $godina = $this->ceoBroj('godina', (int) date('Y'));

        if ($godina < 2000 || $godina > (int) date('Y') + 1) {
            $greske['godina'] = 'Godina nije ispravna.';
        }

        if ($greske !== []) {
            $_SESSION['greske_rad'] = $greske;
            $_SESSION['stari_unos'] = $_POST;
            poruka('greska', 'Ispravite označena polja.');
            $this->preusmeri($id > 0 ? 'admin/radovi/izmeni/' . $id : 'admin/radovi/nov');
        }

        $uslugaId = $this->ceoBroj('usluga_id', 0);

        $noviId = $this->model('Rad')->sacuvaj([
            'id'            => $id > 0 ? $id : null,
            'naziv'         => $naziv,
            'usluga_id'     => $uslugaId > 0 ? $uslugaId : null,
            'opis'          => $opis,
            'slika'         => (string) $this->unos('slika', '') ?: null,
            'lokacija'      => (string) $this->unos('lokacija', ''),
            'kvadratura'    => $this->ceoBroj('kvadratura', 0) ?: null,
            'trajanje_dana' => $this->ceoBroj('trajanje_dana', 0) ?: null,
            'godina'        => $godina,
            'izdvojen'      => $this->unos('izdvojen') ? 1 : 0,
            'objavljen'     => $this->unos('objavljen') ? 1 : 0,
        ]);

        unset($_SESSION['stari_unos']);
        poruka('uspeh', $id > 0 ? 'Izmene su sačuvane.' : 'Novi rad je dodat.');
        $this->preusmeri('admin/radovi/izmeni/' . $noviId);
    }

    // --- Usluge ---------------------------------------------------------

    public function usluge(): void
    {
        $this->prikaz('admin/usluge', [
            'naslovStrane' => 'Usluge - administracija',
            'usluge'       => $this->model('Usluga')->sve(),
        ], 'admin');
    }

    public function sacuvajUslugu(): void
    {
        if (!$this->jePost()) {
            $this->preusmeri('admin/usluge');
        }

        $this->zahtevajCsrf();

        $id    = $this->ceoBroj('id', 0);
        $naziv = (string) $this->unos('naziv', '');

        if (mb_strlen($naziv, 'UTF-8') < 3) {
            poruka('greska', 'Naziv usluge mora imati bar 3 znaka.');
            $this->preusmeri('admin/usluge');
        }

        $model     = $this->model('Usluga');
        $postojeca = $id > 0 ? $model->nadji($id) : null;

        $model->sacuvaj([
            'id'          => $id > 0 ? $id : null,
            'naziv'       => $naziv,
            'slug'        => $postojeca['slug'] ?? slug($naziv),
            'ikona'       => (string) $this->unos('ikona', 'bi-tools'),
            'kratak_opis' => (string) $this->unos('kratak_opis', ''),
            'opis'        => (string) $this->unos('opis', ''),
            'cena_od'     => (float) str_replace(',', '.', (string) $this->unos('cena_od', '0')),
            'jedinica'    => (string) $this->unos('jedinica', 'm2'),
            'slika'       => (string) $this->unos('slika', '') ?: ($postojeca['slika'] ?? null),
            'redosled'    => $this->ceoBroj('redosled', 0),
            'aktivna'     => $this->unos('aktivna') ? 1 : 0,
        ]);

        poruka('uspeh', 'Usluga je sačuvana.');
        $this->preusmeri('admin/usluge');
    }
}
