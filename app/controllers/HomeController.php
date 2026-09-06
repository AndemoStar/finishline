<?php
declare(strict_types=1);

/**
 * Pocetna strana.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $usluge  = $this->model('Usluga');
        $radovi  = $this->model('Rad');
        $utisci  = $this->model('Utisak');

        $this->prikaz('home/index', [
            'naslovStrane' => APP_NAME . ' - ' . APP_SLOGAN . ' u Novom Sadu',
            'opisStrane'   => 'Gletovanje, krečenje, gips i dekorativni malteri. Besplatan izlazak na teren i ponuda u roku od 24 sata.',
            'usluge'       => $usluge->saBrojemRadova(),
            'radovi'       => $radovi->izdvojeni(6),
            'utisci'       => $utisci->odobreni(6),

            // Brojevi se ispisuju vec sa servera, pa su tacni i kada
            // JavaScript nije dostupan. Ajax ih posle samo animira.
            'statistika'   => [
                'godina_iskustva' => 12,
                'radova'          => $radovi->prebrojJavne(),
                'kvadratura'      => $radovi->ukupnaKvadratura(),
                'zadovoljnih'     => $utisci->prebrojOdobrene(),
            ],
        ]);
    }
}
