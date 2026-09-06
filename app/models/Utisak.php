<?php
declare(strict_types=1);

/**
 * Utisci klijenata. Na sajtu se prikazuju samo odobreni.
 */
final class Utisak extends Model
{
    protected string $tabela = 'utisci';

    public function odobreni(int $limit = 6): array
    {
        $limit = max(1, min(30, $limit));

        return $this->sviRedovi(
            "SELECT id, ime, lokacija, tekst, kreiran
             FROM utisci
             WHERE odobren = 1
             ORDER BY id DESC
             LIMIT {$limit}"
        );
    }

    public function svi(): array
    {
        return $this->sviRedovi('SELECT * FROM utisci ORDER BY odobren, id DESC');
    }

    public function prebrojOdobrene(): int
    {
        return (int) $this->jednaVrednost('SELECT COUNT(*) FROM utisci WHERE odobren = 1');
    }

    public function promeniOdobrenje(int $id, bool $odobren): bool
    {
        return $this->upit(
            'UPDATE utisci SET odobren = ? WHERE id = ?',
            [$odobren ? 1 : 0, $id]
        )->rowCount() > 0;
    }

    public function napravi(string $ime, string $lokacija, string $tekst): int
    {
        $this->upit(
            'INSERT INTO utisci (ime, lokacija, tekst, odobren, kreiran)
             VALUES (?, ?, ?, 0, NOW())',
            [$ime, $lokacija, $tekst]
        );

        return $this->poslednjiId();
    }
}
