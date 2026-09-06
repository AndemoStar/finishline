<?php
declare(strict_types=1);

/**
 * Usluge koje firma nudi (gletovanje, krecenje, gips...).
 */
final class Usluga extends Model
{
    protected string $tabela = 'usluge';

    /** Sve aktivne usluge, redom koji je zadao administrator. */
    public function aktivne(?int $limit = null): array
    {
        $sql = 'SELECT * FROM usluge WHERE aktivna = 1 ORDER BY redosled, naziv';

        if ($limit !== null) {
            // Limit se ne moze vezati kao parametar, zato se pretvara u ceo broj
            $sql .= ' LIMIT ' . max(1, (int) $limit);
        }

        return $this->sviRedovi($sql);
    }

    public function sve(): array
    {
        return $this->sviRedovi('SELECT * FROM usluge ORDER BY redosled, naziv');
    }

    public function poSlugu(string $slug): ?array
    {
        return $this->jedanRed(
            'SELECT * FROM usluge WHERE slug = ? AND aktivna = 1 LIMIT 1',
            [$slug]
        );
    }

    /** Usluge sa brojem izvedenih radova - podaci za pocetnu stranu. */
    public function saBrojemRadova(): array
    {
        return $this->sviRedovi(
            'SELECT u.*, COUNT(r.id) AS broj_radova
             FROM usluge u
             LEFT JOIN radovi r ON r.usluga_id = u.id
             WHERE u.aktivna = 1
             GROUP BY u.id
             ORDER BY u.redosled, u.naziv'
        );
    }

    public function sacuvaj(array $p): int
    {
        if (!empty($p['id'])) {
            $this->upit(
                'UPDATE usluge
                 SET naziv = ?, slug = ?, ikona = ?, kratak_opis = ?, opis = ?,
                     cena_od = ?, jedinica = ?, slika = ?, redosled = ?, aktivna = ?
                 WHERE id = ?',
                [
                    $p['naziv'], $p['slug'], $p['ikona'], $p['kratak_opis'], $p['opis'],
                    $p['cena_od'], $p['jedinica'], $p['slika'], $p['redosled'], $p['aktivna'],
                    (int) $p['id'],
                ]
            );

            return (int) $p['id'];
        }

        $this->upit(
            'INSERT INTO usluge (naziv, slug, ikona, kratak_opis, opis, cena_od, jedinica, slika, redosled, aktivna)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $p['naziv'], $p['slug'], $p['ikona'], $p['kratak_opis'], $p['opis'],
                $p['cena_od'], $p['jedinica'], $p['slika'], $p['redosled'], $p['aktivna'],
            ]
        );

        return $this->poslednjiId();
    }

    public function prebrojAktivne(): int
    {
        return (int) $this->jednaVrednost('SELECT COUNT(*) FROM usluge WHERE aktivna = 1');
    }
}
