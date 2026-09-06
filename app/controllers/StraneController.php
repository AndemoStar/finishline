<?php
declare(strict_types=1);

/**
 * Staticne stranice sa sadrzajem iz baze (o firmi).
 */
final class StraneController extends Controller
{
    public function oNama(): void
    {
        $radovi = $this->model('Rad');
        $utisci = $this->model('Utisak');

        $this->prikaz('strane/o-nama', [
            'naslovStrane'  => 'O nama - ' . APP_NAME,
            'opisStrane'    => 'Ekipa za završne građevinske radove iz Novog Sada. Kako radimo, čime garantujemo kvalitet i zašto poštujemo rokove.',
            'brojRadova'    => $radovi->prebrojJavne(),
            'kvadratura'    => $radovi->ukupnaKvadratura(),
            'brojUtisaka'   => $utisci->prebrojOdobrene(),
            'usluge'        => $this->model('Usluga')->aktivne(),
        ]);
    }
}
