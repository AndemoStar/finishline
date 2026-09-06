<?php
declare(strict_types=1);

/**
 * Izvedeni radovi - portfolio firme (galerija).
 */
final class Rad extends Model
{
    protected string $tabela = 'radovi';

    /**
     * Radovi za galeriju, sa mogucnoscu filtriranja po usluzi.
     * Koristi ga i web servis GET api/radovi (Ajax filtriranje).
     */
    public function javni(?int $uslugaId = null, int $limit = 9, int $pomak = 0): array
    {
        $uslovi     = ['r.objavljen = 1'];
        $parametri  = [];

        if ($uslugaId !== null && $uslugaId > 0) {
            $uslovi[]    = 'r.usluga_id = ?';
            $parametri[] = $uslugaId;
        }

        $gde = implode(' AND ', $uslovi);

        // LIMIT i OFFSET se pretvaraju u cele brojeve, pa nije moguca injekcija
        $limit = max(1, min(60, $limit));
        $pomak = max(0, $pomak);

        return $this->sviRedovi(
            "SELECT r.*, u.naziv AS usluga_naziv, u.slug AS usluga_slug
             FROM radovi r
             LEFT JOIN usluge u ON u.id = r.usluga_id
             WHERE {$gde}
             ORDER BY r.izdvojen DESC, r.godina DESC, r.id DESC
             LIMIT {$limit} OFFSET {$pomak}",
            $parametri
        );
    }

    public function prebrojJavne(?int $uslugaId = null): int
    {
        if ($uslugaId !== null && $uslugaId > 0) {
            return (int) $this->jednaVrednost(
                'SELECT COUNT(*) FROM radovi WHERE objavljen = 1 AND usluga_id = ?',
                [$uslugaId]
            );
        }

        return (int) $this->jednaVrednost('SELECT COUNT(*) FROM radovi WHERE objavljen = 1');
    }

    public function izdvojeni(int $limit = 6): array
    {
        $limit = max(1, min(24, $limit));

        return $this->sviRedovi(
            "SELECT r.*, u.naziv AS usluga_naziv
             FROM radovi r
             LEFT JOIN usluge u ON u.id = r.usluga_id
             WHERE r.objavljen = 1
             ORDER BY r.izdvojen DESC, r.id DESC
             LIMIT {$limit}"
        );
    }

    public function saUslugom(int $id): ?array
    {
        return $this->jedanRed(
            'SELECT r.*, u.naziv AS usluga_naziv, u.slug AS usluga_slug
             FROM radovi r
             LEFT JOIN usluge u ON u.id = r.usluga_id
             WHERE r.id = ?',
            [$id]
        );
    }

    /** Radovi vezani za jednu uslugu - prikazuju se na strani usluge. */
    public function poUsluzi(int $uslugaId, int $limit = 6): array
    {
        $limit = max(1, min(24, $limit));

        return $this->sviRedovi(
            "SELECT * FROM radovi
             WHERE objavljen = 1 AND usluga_id = ?
             ORDER BY id DESC
             LIMIT {$limit}",
            [$uslugaId]
        );
    }

    public function sviZaAdmina(): array
    {
        return $this->sviRedovi(
            'SELECT r.*, u.naziv AS usluga_naziv
             FROM radovi r
             LEFT JOIN usluge u ON u.id = r.usluga_id
             ORDER BY r.id DESC'
        );
    }

    public function sacuvaj(array $p): int
    {
        if (!empty($p['id'])) {
            $this->upit(
                'UPDATE radovi
                 SET naziv = ?, usluga_id = ?, opis = ?, slika = ?, lokacija = ?,
                     kvadratura = ?, trajanje_dana = ?, godina = ?, izdvojen = ?, objavljen = ?
                 WHERE id = ?',
                [
                    $p['naziv'], $p['usluga_id'], $p['opis'], $p['slika'], $p['lokacija'],
                    $p['kvadratura'], $p['trajanje_dana'], $p['godina'], $p['izdvojen'], $p['objavljen'],
                    (int) $p['id'],
                ]
            );

            return (int) $p['id'];
        }

        $this->upit(
            'INSERT INTO radovi (naziv, usluga_id, opis, slika, lokacija, kvadratura, trajanje_dana, godina, izdvojen, objavljen, kreiran)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $p['naziv'], $p['usluga_id'], $p['opis'], $p['slika'], $p['lokacija'],
                $p['kvadratura'], $p['trajanje_dana'], $p['godina'], $p['izdvojen'], $p['objavljen'],
            ]
        );

        return $this->poslednjiId();
    }

    /** Brisanje rada zajedno sa pripadajucim fotografijama sa diska. */
    public function obrisiSaSlikama(int $id): bool
    {
        $rad = $this->nadji($id);

        if ($rad === null) {
            return false;
        }

        foreach ($this->slike($id) as $s) {
            Upload::ukloni($s['putanja']);
        }

        Upload::ukloni($rad['slika'] ?? null);

        $this->upit('DELETE FROM rad_slike WHERE rad_id = ?', [$id]);

        return $this->obrisi($id);
    }

    // --- Dodatne fotografije rada ---------------------------------------

    public function slike(int $radId): array
    {
        return $this->sviRedovi(
            'SELECT * FROM rad_slike WHERE rad_id = ? ORDER BY redosled, id',
            [$radId]
        );
    }

    public function dodajSliku(int $radId, string $putanja, string $opis = ''): int
    {
        $this->upit(
            'INSERT INTO rad_slike (rad_id, putanja, opis, redosled)
             VALUES (?, ?, ?, (SELECT COALESCE(MAX(x.redosled), 0) + 1 FROM (SELECT redosled FROM rad_slike WHERE rad_id = ?) AS x))',
            [$radId, $putanja, $opis, $radId]
        );

        return $this->poslednjiId();
    }

    public function obrisiSliku(int $slikaId): bool
    {
        $slika = $this->jedanRed('SELECT * FROM rad_slike WHERE id = ?', [$slikaId]);

        if ($slika === null) {
            return false;
        }

        Upload::ukloni($slika['putanja']);

        return $this->upit('DELETE FROM rad_slike WHERE id = ?', [$slikaId])->rowCount() > 0;
    }

    // --- Podaci za brojace na pocetnoj strani ---------------------------

    public function ukupnaKvadratura(): int
    {
        return (int) $this->jednaVrednost('SELECT COALESCE(SUM(kvadratura), 0) FROM radovi WHERE objavljen = 1');
    }
}
