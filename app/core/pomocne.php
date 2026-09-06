<?php
declare(strict_types=1);

/**
 * Male pomocne funkcije koje se koriste u prikazima (view-ovima).
 */

/**
 * Priprema tekst za bezbedan ispis u HTML-u.
 * Sve sto dolazi od korisnika mora proci kroz ovu funkciju - zastita od XSS.
 */
function e($tekst): string
{
    return htmlspecialchars((string) $tekst, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Puna adresa unutar aplikacije. */
function url(string $putanja = ''): string
{
    return BASE_URL . ltrim($putanja, '/');
}

/** Adresa statickog fajla (css, js, slika). */
function asset(string $putanja): string
{
    return BASE_URL . 'assets/' . ltrim($putanja, '/');
}

/**
 * Adresa slike. Ako slika ne postoji, vraca se rezervna slika,
 * da prikaz nikad ne ostane sa polomljenom slikom.
 */
function slika(?string $putanja, string $rezervna = 'img/placeholder.svg'): string
{
    if ($putanja === null || trim($putanja) === '') {
        return asset($rezervna);
    }

    $putanja = ltrim($putanja, '/');

    if (is_file(APP_ROOT . '/assets/' . $putanja)) {
        return asset($putanja);
    }

    return asset($rezervna);
}

/** Da li je prosledjena ruta trenutno aktivna (za isticanje u meniju). */
function aktivna(string $ruta): bool
{
    $trenutna = trim($GLOBALS['TRENUTNA_RUTA'] ?? '', '/');
    $ruta     = trim($ruta, '/');

    if ($ruta === '') {
        return $trenutna === '';
    }

    return $trenutna === $ruta || strpos($trenutna, $ruta . '/') === 0;
}

/** Skracivanje teksta na zadati broj karaktera, bez sasecanja reci. */
function skrati(string $tekst, int $duzina = 120): string
{
    $tekst = trim(strip_tags($tekst));

    if (mb_strlen($tekst, 'UTF-8') <= $duzina) {
        return $tekst;
    }

    $skraceno = mb_substr($tekst, 0, $duzina, 'UTF-8');
    $razmak   = mb_strrpos($skraceno, ' ', 0, 'UTF-8');

    if ($razmak !== false) {
        $skraceno = mb_substr($skraceno, 0, $razmak, 'UTF-8');
    }

    return $skraceno . '...';
}

/** Format cene, npr. 1250 -> "1.250 RSD". */
function cena($iznos, string $valuta = 'RSD'): string
{
    return number_format((float) $iznos, 0, ',', '.') . ' ' . $valuta;
}

/** Datum u obliku 06.09.2026. */
function datum(?string $datum): string
{
    if (empty($datum)) {
        return '-';
    }

    $ts = strtotime($datum);
    return $ts === false ? '-' : date('d.m.Y.', $ts);
}

/**
 * Jednokratna poruka koja prezivi preusmeravanje (flash poruka).
 */
function poruka(?string $tip = null, ?string $tekst = null)
{
    if ($tip !== null && $tekst !== null) {
        $_SESSION['poruke'][] = ['tip' => $tip, 'tekst' => $tekst];
        return null;
    }

    $poruke = $_SESSION['poruke'] ?? [];
    unset($_SESSION['poruke']);
    return $poruke;
}

/** Vrednost polja forme posle neuspesne validacije. */
function staro(string $polje, string $podrazumevano = ''): string
{
    return e($_SESSION['stari_unos'][$polje] ?? $podrazumevano);
}

/** Pretvara naslov u slug: "Gletovanje zidova" -> "gletovanje-zidova". */
function slug(string $tekst): string
{
    $mapa = [
        'č' => 'c', 'ć' => 'c', 'ž' => 'z', 'š' => 's', 'đ' => 'dj',
        'Č' => 'C', 'Ć' => 'C', 'Ž' => 'Z', 'Š' => 'S', 'Đ' => 'Dj',
    ];

    $tekst = strtr($tekst, $mapa);
    $tekst = mb_strtolower($tekst, 'UTF-8');
    $tekst = preg_replace('/[^a-z0-9]+/', '-', $tekst) ?? '';

    return trim($tekst, '-');
}
