<?php
declare(strict_types=1);

/**
 * Pregled usluga i strana pojedinacne usluge.
 */
final class UslugeController extends Controller
{
    public function index(): void
    {
        $this->prikaz('usluge/index', [
            'naslovStrane' => 'Usluge i cene - ' . APP_NAME,
            'opisStrane'   => 'Cenovnik završnih građevinskih radova: gletovanje, krečenje, gips, dekorativni malteri i pregradni zidovi.',
            'usluge'       => $this->model('Usluga')->saBrojemRadova(),
        ]);
    }

    /** Strana jedne usluge, adresa oblika usluge/gletovanje. */
    public function prikazi(string $slug): void
    {
        $model  = $this->model('Usluga');
        $usluga = $model->poSlugu($slug);

        if ($usluga === null) {
            (new GreskaController())->nepostojeca();
            return;
        }

        $this->prikaz('usluge/prikaz', [
            'naslovStrane' => $usluga['naziv'] . ' - ' . APP_NAME,
            'opisStrane'   => $usluga['kratak_opis'],
            'usluga'       => $usluga,
            'radovi'       => $this->model('Rad')->poUsluzi((int) $usluga['id'], 6),
            'ostale'       => $model->aktivne(),
        ]);
    }
}
