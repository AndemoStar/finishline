<?php
declare(strict_types=1);

/**
 * Galerija izvedenih radova.
 *
 * Prva strana radova se iscrtava na serveru (da je vide i pretrazivaci),
 * a filtriranje i dovlacenje sledecih se radi Ajax-om preko api/radovi.
 */
final class RadoviController extends Controller
{
    private const PO_STRANI = 9;

    public function index(): void
    {
        $model  = $this->model('Rad');
        $usluge = $this->model('Usluga')->aktivne();

        $uslugaId = $this->ceoBroj('usluga', 0);
        $uslugaId = $uslugaId > 0 ? $uslugaId : null;

        $radovi = $model->javni($uslugaId, self::PO_STRANI, 0);
        $ukupno = $model->prebrojJavne($uslugaId);

        $this->prikaz('radovi/index', [
            'naslovStrane' => 'Naši radovi - ' . APP_NAME,
            'opisStrane'   => 'Fotografije izvedenih radova: gletovanje, krečenje, spušteni plafoni i dekorativne obrade.',
            'radovi'       => $radovi,
            'usluge'       => $usluge,
            'izabrana'     => $uslugaId,
            'ukupno'       => $ukupno,
            'poStrani'     => self::PO_STRANI,
        ]);
    }

    /** Strana jednog rada sa svim fotografijama. */
    public function prikazi(string $id): void
    {
        $model = $this->model('Rad');
        $rad   = $model->saUslugom((int) $id);

        if ($rad === null || (int) $rad['objavljen'] !== 1) {
            (new GreskaController())->nepostojeca();
            return;
        }

        $this->prikaz('radovi/prikaz', [
            'naslovStrane' => $rad['naziv'] . ' - ' . APP_NAME,
            'opisStrane'   => skrati((string) $rad['opis'], 160),
            'rad'          => $rad,
            'slike'        => $model->slike((int) $rad['id']),
            'slicni'       => $rad['usluga_id'] ? $model->poUsluzi((int) $rad['usluga_id'], 4) : [],
        ]);
    }
}
