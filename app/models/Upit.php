<?php
declare(strict_types=1);

/**
 * Upiti posetilaca - zahtevi za ponudu poslati sa sajta.
 */
final class Upit extends Model
{
    protected string $tabela = 'upiti';

    public const STATUSI = ['nov', 'u_obradi', 'zavrsen', 'odbijen'];

    /**
     * Provera podataka iz forme za upit.
     * Koristi je i obicna forma i web servis, da pravila budu na jednom mestu.
     *
     * @return array<string,string> poruke o greskama po poljima (prazno ako je sve u redu)
     */
    public static function proveri(array $p): array
    {
        $greske = [];

        $ime = trim((string) ($p['ime'] ?? ''));
        if (mb_strlen($ime, 'UTF-8') < 2) {
            $greske['ime'] = 'Unesite ime i prezime.';
        } elseif (mb_strlen($ime, 'UTF-8') > 100) {
            $greske['ime'] = 'Ime je predugacko.';
        }

        $email = trim((string) ($p['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $greske['email'] = 'Unesite ispravnu email adresu.';
        }

        $telefon = trim((string) ($p['telefon'] ?? ''));
        if ($telefon !== '' && !preg_match('/^[0-9 +().-]{6,40}$/', $telefon)) {
            $greske['telefon'] = 'Telefon sme sadrzati samo brojeve i znakove + ( ) - .';
        }

        $poruka = trim((string) ($p['poruka'] ?? ''));
        if (mb_strlen($poruka, 'UTF-8') < 10) {
            $greske['poruka'] = 'Opisite ukratko sta vam je potrebno (najmanje 10 znakova).';
        } elseif (mb_strlen($poruka, 'UTF-8') > 2000) {
            $greske['poruka'] = 'Poruka je predugacka.';
        }

        $kvadratura = $p['kvadratura'] ?? '';
        if ($kvadratura !== '' && $kvadratura !== null) {
            $k = (int) $kvadratura;
            if ($k < 1 || $k > 100000) {
                $greske['kvadratura'] = 'Kvadratura mora biti izmedju 1 i 100000.';
            }
        }

        // Skriveno polje koje ljudi ne popunjavaju - ako je puno, u pitanju je bot
        if (trim((string) ($p['website'] ?? '')) !== '') {
            $greske['website'] = 'Zahtev je odbijen.';
        }

        return $greske;
    }

    public function napravi(array $p): int
    {
        $this->upit(
            'INSERT INTO upiti (ime, email, telefon, usluga_id, kvadratura, poruka, procena, status, ip_adresa, kreiran)
             VALUES (?, ?, ?, ?, ?, ?, ?, "nov", ?, NOW())',
            [
                $p['ime'],
                $p['email'],
                $p['telefon'],
                $p['usluga_id'] !== null ? (int) $p['usluga_id'] : null,
                $p['kvadratura'] !== null ? (int) $p['kvadratura'] : null,
                $p['poruka'],
                $p['procena'] ?? null,
                substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
            ]
        );

        return $this->poslednjiId();
    }

    /** Upiti za administraciju, opciono filtrirani po statusu. */
    public function svi(?string $status = null): array
    {
        if ($status !== null && in_array($status, self::STATUSI, true)) {
            return $this->sviRedovi(
                'SELECT p.*, u.naziv AS usluga_naziv
                 FROM upiti p
                 LEFT JOIN usluge u ON u.id = p.usluga_id
                 WHERE p.status = ?
                 ORDER BY p.id DESC',
                [$status]
            );
        }

        return $this->sviRedovi(
            'SELECT p.*, u.naziv AS usluga_naziv
             FROM upiti p
             LEFT JOIN usluge u ON u.id = p.usluga_id
             ORDER BY p.id DESC'
        );
    }

    public function promeniStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSI, true)) {
            return false;
        }

        return $this->upit(
            'UPDATE upiti SET status = ? WHERE id = ?',
            [$status, $id]
        )->rowCount() > 0;
    }

    public function brojPoStatusu(string $status): int
    {
        if (!in_array($status, self::STATUSI, true)) {
            return 0;
        }

        return (int) $this->jednaVrednost('SELECT COUNT(*) FROM upiti WHERE status = ?', [$status]);
    }

    /** Broj upita po danima - podaci za mali grafikon u administraciji. */
    public function poDanima(int $dana = 14): array
    {
        $dana = max(1, min(60, $dana));

        return $this->sviRedovi(
            "SELECT DATE(kreiran) AS dan, COUNT(*) AS broj
             FROM upiti
             WHERE kreiran >= DATE_SUB(CURDATE(), INTERVAL {$dana} DAY)
             GROUP BY DATE(kreiran)
             ORDER BY dan"
        );
    }

    /**
     * Prosta zastita od slanja previse upita sa iste adrese.
     * Vraca broj upita poslatih u poslednjih sat vremena.
     */
    public function skorasnjiSaIste(string $ip): int
    {
        return (int) $this->jednaVrednost(
            'SELECT COUNT(*) FROM upiti
             WHERE ip_adresa = ? AND kreiran >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
            [substr($ip, 0, 45)]
        );
    }
}
