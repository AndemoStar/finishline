<?php
declare(strict_types=1);

/**
 * Prijem i cuvanje otpremljenih slika.
 *
 * Koristi ga web servis POST api/upload. Provera je viseslojna:
 *  - velicina fajla,
 *  - stvarni tip sadrzaja (getimagesize), a ne ime fajla koje salje korisnik,
 *  - dozvoljena lista formata,
 *  - novo, nasumicno ime fajla (korisnik ne bira ime na serveru),
 *  - u folderu za slike je onemoguceno izvrsavanje PHP-a (.htaccess).
 */
final class Upload
{
    /** Dozvoljeni tipovi: stvarni MIME => ekstenzija. */
    private const DOZVOLJENI = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Cuva jednu otpremljenu sliku.
     *
     * @param array  $fajl  jedan element iz $_FILES
     * @param string $grupa podfolder, npr. 'radovi'
     *
     * @return array{uspeh:bool, poruka?:string, putanja?:string, url?:string, naziv?:string, velicina?:int}
     */
    public static function slika(array $fajl, string $grupa = 'radovi'): array
    {
        // 1) Greske koje prijavljuje sam PHP
        $kod = $fajl['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($kod !== UPLOAD_ERR_OK) {
            return ['uspeh' => false, 'poruka' => self::opisGreske((int) $kod)];
        }

        // 2) Fajl mora zaista biti stigao kroz HTTP upload
        if (!is_uploaded_file($fajl['tmp_name'])) {
            return ['uspeh' => false, 'poruka' => 'Neispravan zahtev za otpremanje.'];
        }

        // 3) Velicina
        $velicina = (int) ($fajl['size'] ?? 0);

        if ($velicina <= 0) {
            return ['uspeh' => false, 'poruka' => 'Fajl je prazan.'];
        }

        if ($velicina > UPLOAD_MAX_BYTES) {
            $mb = round(UPLOAD_MAX_BYTES / 1048576, 1);
            return ['uspeh' => false, 'poruka' => 'Slika je prevelika. Najvise ' . $mb . ' MB.'];
        }

        // 4) Stvarni tip sadrzaja - ne verujemo imenu fajla ni poslatom MIME tipu
        $info = @getimagesize($fajl['tmp_name']);

        if ($info === false) {
            return ['uspeh' => false, 'poruka' => 'Poslati fajl nije slika.'];
        }

        $mime = strtolower((string) ($info['mime'] ?? ''));

        if (!isset(self::DOZVOLJENI[$mime])) {
            return ['uspeh' => false, 'poruka' => 'Dozvoljeni formati su JPG, PNG, WEBP i GIF.'];
        }

        // 5) Novo ime fajla - nasumicno, bez ijednog znaka od korisnika
        $ekstenzija = self::DOZVOLJENI[$mime];
        $noviNaziv  = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $ekstenzija;

        $podfolder = preg_replace('/[^a-z0-9_-]/', '', strtolower($grupa)) ?: 'ostalo';
        $folder    = UPLOAD_DIR . '/' . $podfolder;

        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
            return ['uspeh' => false, 'poruka' => 'Nije moguce napraviti folder za slike.'];
        }

        $odrediste = $folder . '/' . $noviNaziv;

        if (!move_uploaded_file($fajl['tmp_name'], $odrediste)) {
            return ['uspeh' => false, 'poruka' => 'Cuvanje slike nije uspelo.'];
        }

        @chmod($odrediste, 0644);

        // Putanja koja se upisuje u bazu je relativna u odnosu na assets/
        $relativna = 'uploads/' . $podfolder . '/' . $noviNaziv;

        return [
            'uspeh'    => true,
            'putanja'  => $relativna,
            'url'      => asset($relativna),
            'naziv'    => $noviNaziv,
            'velicina' => $velicina,
            'sirina'   => (int) ($info[0] ?? 0),
            'visina'   => (int) ($info[1] ?? 0),
        ];
    }

    /** Brisanje slike sa diska (npr. kada se obrise rad iz galerije). */
    public static function ukloni(?string $relativnaPutanja): bool
    {
        if (empty($relativnaPutanja) || strpos($relativnaPutanja, 'uploads/') !== 0) {
            return false;
        }

        // Zastita od izlaska iz foldera (path traversal)
        if (strpos($relativnaPutanja, '..') !== false) {
            return false;
        }

        $puna = APP_ROOT . '/assets/' . $relativnaPutanja;

        return is_file($puna) ? @unlink($puna) : false;
    }

    private static function opisGreske(int $kod): string
    {
        switch ($kod) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Slika je veca od dozvoljene velicine.';
            case UPLOAD_ERR_PARTIAL:
                return 'Slika je otpremljena samo delimicno.';
            case UPLOAD_ERR_NO_FILE:
                return 'Nije izabrana nijedna slika.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Na serveru nedostaje privremeni folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Upis na disk nije uspeo.';
            default:
                return 'Otpremanje nije uspelo.';
        }
    }
}
