<?php
declare(strict_types=1);

/**
 * Web servisi aplikacije.
 *
 * Svi odgovori su u JSON formatu, u obliku:
 *   { "uspeh": true|false, "podaci": ..., "poruka": "..." }
 *
 * Servise koristi JavaScript na sajtu (Ajax), ali se mogu pozvati i
 * spolja, npr. iz Postman-a:
 *
 *   GET  api/usluge                    - lista usluga
 *   GET  api/radovi?usluga=3&limit=9   - radovi, sa filtriranjem i strananjem
 *   GET  api/radovi/5                  - jedan rad sa fotografijama
 *   GET  api/statistika                - brojevi za pocetnu stranu
 *   POST api/procena                   - procena cene na osnovu kvadrature
 *   POST api/upiti                     - slanje upita sa sajta
 *   GET  api/upiti                     - lista upita          (samo admin)
 *   POST api/upiti/{id}/status         - promena statusa upita (samo admin)
 *   POST api/upload                    - otpremanje fotografije (samo admin)
 */
final class ApiController extends Controller
{
    // =================================================================
    //  Javni servisi
    // =================================================================

    /** GET api/usluge */
    public function usluge(): void
    {
        $this->samoMetoda(['GET']);

        $usluge = $this->model('Usluga')->aktivne();

        $this->json([
            'uspeh'  => true,
            'ukupno' => count($usluge),
            'podaci' => array_map([$this, 'uslugaZaJson'], $usluge),
        ]);
    }

    /** GET api/radovi?usluga=3&limit=9&pomak=0 */
    public function radovi(): void
    {
        $this->samoMetoda(['GET']);

        $model = $this->model('Rad');

        $uslugaId = $this->ceoBroj('usluga', 0);
        $uslugaId = $uslugaId > 0 ? $uslugaId : null;

        $limit = $this->ceoBroj('limit', 9);
        $pomak = $this->ceoBroj('pomak', 0);

        $radovi = $model->javni($uslugaId, $limit, $pomak);
        $ukupno = $model->prebrojJavne($uslugaId);

        $this->json([
            'uspeh'    => true,
            'ukupno'   => $ukupno,
            'pomak'    => $pomak,
            'ima_jos'  => ($pomak + count($radovi)) < $ukupno,
            'podaci'   => array_map([$this, 'radZaJson'], $radovi),
        ]);
    }

    /** GET api/radovi/{id} */
    public function rad(string $id): void
    {
        $metoda = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // DELETE api/radovi/{id} - brisanje rada, samo za administratora
        if ($metoda === 'DELETE' || ($metoda === 'POST' && $this->unos('_metoda') === 'DELETE')) {
            $this->obrisiRad((int) $id);
            return;
        }

        $this->samoMetoda(['GET']);

        $model = $this->model('Rad');
        $rad   = $model->saUslugom((int) $id);

        if ($rad === null || (int) $rad['objavljen'] !== 1) {
            $this->json(['uspeh' => false, 'poruka' => 'Rad nije pronađen.'], 404);
        }

        $podaci          = $this->radZaJson($rad);
        $podaci['opis']  = (string) $rad['opis'];
        $podaci['slike'] = array_map(static function (array $s): array {
            return [
                'id'   => (int) $s['id'],
                'url'  => slika($s['putanja']),
                'opis' => (string) $s['opis'],
            ];
        }, $model->slike((int) $rad['id']));

        $this->json(['uspeh' => true, 'podaci' => $podaci]);
    }

    /** GET api/statistika - brojevi koje pocetna strana animira. */
    public function statistika(): void
    {
        $this->samoMetoda(['GET']);

        $radovi = $this->model('Rad');
        $usluge = $this->model('Usluga');

        $this->json([
            'uspeh'  => true,
            'podaci' => [
                'radova'       => $radovi->prebrojJavne(),
                'kvadratura'   => $radovi->ukupnaKvadratura(),
                'usluga'       => $usluge->prebrojAktivne(),
                'godina_iskustva' => 12,
            ],
        ]);
    }

    /**
     * POST api/procena
     * Racuna okvirnu cenu na osnovu kvadrature i izabranih usluga.
     */
    public function procena(): void
    {
        $this->samoMetoda(['POST']);

        $kvadratura = $this->ceoBroj('kvadratura', 0);
        $izabrane   = $_POST['usluge'] ?? [];

        if ($kvadratura < 1 || $kvadratura > 100000) {
            $this->json(['uspeh' => false, 'poruka' => 'Unesite kvadraturu između 1 i 100000.'], 422);
        }

        if (!is_array($izabrane) || $izabrane === []) {
            $this->json(['uspeh' => false, 'poruka' => 'Izaberite bar jednu uslugu.'], 422);
        }

        $model  = $this->model('Usluga');
        $stavke = [];
        $ukupno = 0.0;

        foreach ($izabrane as $id) {
            $usluga = $model->nadji((int) $id);

            if ($usluga === null || (int) $usluga['aktivna'] !== 1) {
                continue;
            }

            $iznos    = (float) $usluga['cena_od'] * $kvadratura;
            $ukupno  += $iznos;

            $stavke[] = [
                'id'       => (int) $usluga['id'],
                'naziv'    => $usluga['naziv'],
                'cena_od'  => (float) $usluga['cena_od'],
                'jedinica' => $usluga['jedinica'],
                'iznos'    => $iznos,
                'prikaz'   => cena($iznos),
            ];
        }

        if ($stavke === []) {
            $this->json(['uspeh' => false, 'poruka' => 'Izabrane usluge nisu pronađene.'], 422);
        }

        $this->json([
            'uspeh'  => true,
            'podaci' => [
                'kvadratura'   => $kvadratura,
                'stavke'       => $stavke,
                'ukupno'       => $ukupno,
                'ukupno_prikaz'=> cena($ukupno),
                'napomena'     => 'Procena je okvirna i ne obuhvata materijal. Tačna ponuda se daje posle izlaska na teren.',
            ],
        ]);
    }

    // =================================================================
    //  Upiti
    // =================================================================

    /**
     * POST api/upiti - slanje upita sa sajta (javno, uz CSRF token)
     * GET  api/upiti - lista upita (samo administrator)
     */
    public function upiti(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $this->listaUpita();
            return;
        }

        $this->samoMetoda(['POST']);
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
            $this->json([
                'uspeh'  => false,
                'poruka' => 'Molimo ispravite označena polja.',
                'greske' => $greske,
            ], 422);
        }

        $model = $this->model('Upit');

        if ($model->skorasnjiSaIste((string) ($_SERVER['REMOTE_ADDR'] ?? '')) >= 5) {
            $this->json([
                'uspeh'  => false,
                'poruka' => 'Poslali ste previše upita. Pokušajte kasnije ili nas pozovite.',
            ], 429);
        }

        $procena = $this->unos('procena', '');

        $id = $model->napravi([
            'ime'        => $podaci['ime'],
            'email'      => $podaci['email'],
            'telefon'    => $podaci['telefon'],
            'usluga_id'  => $podaci['usluga_id'] !== '' ? (int) $podaci['usluga_id'] : null,
            'kvadratura' => $podaci['kvadratura'] !== '' ? (int) $podaci['kvadratura'] : null,
            'poruka'     => $podaci['poruka'],
            'procena'    => $procena !== '' ? (float) $procena : null,
        ]);

        $this->json([
            'uspeh'  => true,
            'poruka' => 'Hvala! Upit je primljen, javljamo se u roku od 24 sata.',
            'podaci' => ['id' => $id],
        ], 201);
    }

    /** POST api/upiti/{id}/status */
    public function statusUpita(string $id): void
    {
        $this->samoMetoda(['POST']);
        $this->zahtevajAdminaJson();
        $this->zahtevajCsrf();

        $status = (string) $this->unos('status', '');

        if (!$this->model('Upit')->promeniStatus((int) $id, $status)) {
            $this->json(['uspeh' => false, 'poruka' => 'Status nije promenjen.'], 422);
        }

        $this->json([
            'uspeh'  => true,
            'poruka' => 'Status je sačuvan.',
            'podaci' => ['id' => (int) $id, 'status' => $status],
        ]);
    }

    private function listaUpita(): void
    {
        $this->zahtevajAdminaJson();

        $status = (string) $this->unos('status', '');
        $status = $status !== '' ? $status : null;

        $upiti = $this->model('Upit')->svi($status);

        $this->json([
            'uspeh'  => true,
            'ukupno' => count($upiti),
            'podaci' => array_map(static function (array $u): array {
                return [
                    'id'         => (int) $u['id'],
                    'ime'        => $u['ime'],
                    'email'      => $u['email'],
                    'telefon'    => $u['telefon'],
                    'usluga'     => $u['usluga_naziv'],
                    'kvadratura' => $u['kvadratura'] !== null ? (int) $u['kvadratura'] : null,
                    'poruka'     => $u['poruka'],
                    'status'     => $u['status'],
                    'datum'      => datum($u['kreiran']),
                ];
            }, $upiti),
        ]);
    }

    // =================================================================
    //  Otpremanje fotografija (samo administrator)
    // =================================================================

    /**
     * POST api/upload
     * Prima jednu sliku (polje "slika"). Ako je poslat i "rad_id",
     * slika se odmah vezuje za taj rad u tabeli rad_slike.
     */
    public function upload(): void
    {
        $this->samoMetoda(['POST']);
        $this->zahtevajAdminaJson();
        $this->zahtevajCsrf();

        if (empty($_FILES['slika'])) {
            $this->json(['uspeh' => false, 'poruka' => 'Nije poslata nijedna slika.'], 422);
        }

        $rezultat = Upload::slika($_FILES['slika'], (string) $this->unos('grupa', 'radovi'));

        if (!$rezultat['uspeh']) {
            $this->json(['uspeh' => false, 'poruka' => $rezultat['poruka']], 422);
        }

        $radId = $this->ceoBroj('rad_id', 0);

        if ($radId > 0) {
            $model = $this->model('Rad');

            if ($model->nadji($radId) === null) {
                Upload::ukloni($rezultat['putanja']);
                $this->json(['uspeh' => false, 'poruka' => 'Rad nije pronađen.'], 404);
            }

            $rezultat['slika_id'] = $model->dodajSliku($radId, $rezultat['putanja'], (string) $this->unos('opis', ''));
        }

        $this->json([
            'uspeh'  => true,
            'poruka' => 'Slika je otpremljena.',
            'podaci' => $rezultat,
        ], 201);
    }

    /**
     * DELETE api/slike/{id}
     * Brise jednu fotografiju rada, i zapis u bazi i fajl na disku.
     */
    public function slika(string $id): void
    {
        $metoda = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($metoda !== 'DELETE' && !($metoda === 'POST' && $this->unos('_metoda') === 'DELETE')) {
            header('Allow: DELETE');
            $this->json(['uspeh' => false, 'poruka' => 'Dozvoljena je samo DELETE metoda.'], 405);
        }

        $this->zahtevajAdminaJson();
        $this->zahtevajCsrf();

        if (!$this->model('Rad')->obrisiSliku((int) $id)) {
            $this->json(['uspeh' => false, 'poruka' => 'Fotografija nije pronađena.'], 404);
        }

        $this->json(['uspeh' => true, 'poruka' => 'Fotografija je obrisana.']);
    }

    /** DELETE api/radovi/{id} */
    private function obrisiRad(int $id): void
    {
        $this->zahtevajAdminaJson();
        $this->zahtevajCsrf();

        if (!$this->model('Rad')->obrisiSaSlikama($id)) {
            $this->json(['uspeh' => false, 'poruka' => 'Rad nije pronađen.'], 404);
        }

        $this->json(['uspeh' => true, 'poruka' => 'Rad je obrisan.']);
    }

    // =================================================================
    //  Pomocne metode
    // =================================================================

    /**
     * Provera CSRF tokena za web servise.
     *
     * Za razliku od obicnih formi, servis nikada ne preusmerava -
     * uvek vraca JSON, pa ga i spoljni klijenti mogu ispravno obraditi.
     */
    protected function zahtevajCsrf(): void
    {
        if (!Csrf::ispravan()) {
            $this->json([
                'uspeh'  => false,
                'poruka' => 'Bezbednosni token nije ispravan ili nedostaje.',
            ], 403);
        }
    }

    /** Odbija zahteve poslate pogresnom HTTP metodom. */
    private function samoMetoda(array $dozvoljene): void
    {
        $metoda = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($metoda, $dozvoljene, true)) {
            header('Allow: ' . implode(', ', $dozvoljene));
            $this->json([
                'uspeh'  => false,
                'poruka' => 'Metoda ' . $metoda . ' nije dozvoljena na ovoj adresi.',
            ], 405);
        }
    }

    /** Kontrola pristupa za servise - odgovor je JSON, ne HTML strana. */
    private function zahtevajAdminaJson(): void
    {
        if (!Auth::prijavljen()) {
            $this->json(['uspeh' => false, 'poruka' => 'Potrebna je prijava.'], 401);
        }

        if (!Auth::jeAdmin()) {
            $this->json(['uspeh' => false, 'poruka' => 'Nemate ovlašćenje za ovu radnju.'], 403);
        }
    }

    private function uslugaZaJson(array $u): array
    {
        return [
            'id'          => (int) $u['id'],
            'naziv'       => $u['naziv'],
            'slug'        => $u['slug'],
            'ikona'       => $u['ikona'],
            'kratak_opis' => $u['kratak_opis'],
            'cena_od'     => (float) $u['cena_od'],
            'jedinica'    => $u['jedinica'],
            'cena_prikaz' => cena($u['cena_od']) . ' / ' . $u['jedinica'],
            'url'         => url('usluge/' . $u['slug']),
        ];
    }

    private function radZaJson(array $r): array
    {
        return [
            'id'         => (int) $r['id'],
            'naziv'      => $r['naziv'],
            'usluga'     => $r['usluga_naziv'] ?? null,
            'usluga_id'  => $r['usluga_id'] !== null ? (int) $r['usluga_id'] : null,
            'lokacija'   => $r['lokacija'],
            'kvadratura' => $r['kvadratura'] !== null ? (int) $r['kvadratura'] : null,
            'godina'     => $r['godina'] !== null ? (int) $r['godina'] : null,
            'izdvojen'   => (int) $r['izdvojen'] === 1,
            'kratak'     => skrati((string) $r['opis'], 110),
            'slika'      => slika($r['slika']),
            'url'        => url('radovi/' . (int) $r['id']),
        ];
    }
}
