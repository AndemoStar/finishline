<?php
declare(strict_types=1);

/**
 * Korisnici koji imaju pristup administraciji.
 */
final class Korisnik extends Model
{
    protected string $tabela = 'korisnici';

    public function poEmailu(string $email): ?array
    {
        return $this->jedanRed(
            'SELECT id, ime, email, lozinka_hash, uloga, aktivan
             FROM korisnici
             WHERE email = ?
             LIMIT 1',
            [mb_strtolower(trim($email), 'UTF-8')]
        );
    }

    public function svi(): array
    {
        return $this->sviRedovi(
            'SELECT id, ime, email, uloga, aktivan, poslednja_prijava, kreiran
             FROM korisnici
             ORDER BY ime'
        );
    }

    /** Cuva novu lozinku kao hash - nikada u citljivom obliku. */
    public function postaviLozinku(int $id, string $lozinka): bool
    {
        $hash = password_hash($lozinka, PASSWORD_DEFAULT);

        return $this->upit(
            'UPDATE korisnici SET lozinka_hash = ? WHERE id = ?',
            [$hash, $id]
        )->rowCount() > 0;
    }

    public function zabeleziPrijavu(int $id): void
    {
        $this->upit('UPDATE korisnici SET poslednja_prijava = NOW() WHERE id = ?', [$id]);
    }

    public function napravi(string $ime, string $email, string $lozinka, string $uloga = 'urednik'): int
    {
        $this->upit(
            'INSERT INTO korisnici (ime, email, lozinka_hash, uloga, aktivan, kreiran)
             VALUES (?, ?, ?, ?, 1, NOW())',
            [
                $ime,
                mb_strtolower(trim($email), 'UTF-8'),
                password_hash($lozinka, PASSWORD_DEFAULT),
                $uloga,
            ]
        );

        return $this->poslednjiId();
    }
}
