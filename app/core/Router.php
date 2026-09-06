<?php
declare(strict_types=1);

/**
 * Ruter - preslikava adresu iz pregledaca na kontroler i njegovu metodu.
 *
 * Rute se pisu kao 'usluge/{slug}'. Deo u viticastim zagradama je
 * parametar koji se prosledjuje metodi kontrolera.
 */
final class Router
{
    /** @var array<string, array{0:string,1:string}> */
    private array $rute = [];

    public function dodaj(string $putanja, string $kontroler, string $metoda): void
    {
        $this->rute[trim($putanja, '/')] = [$kontroler, $metoda];
    }

    /**
     * Pronalazi rutu koja odgovara adresi.
     *
     * @return array{kontroler:string, metoda:string, parametri:array}|null
     */
    public function pronadji(string $url): ?array
    {
        $url = trim($url, '/');

        // 1) Tacno poklapanje - najbrze i najcesce
        if (isset($this->rute[$url])) {
            return [
                'kontroler' => $this->rute[$url][0],
                'metoda'    => $this->rute[$url][1],
                'parametri' => [],
            ];
        }

        // 2) Rute sa parametrima, npr. usluge/{slug}
        $delovi = $url === '' ? [] : explode('/', $url);

        foreach ($this->rute as $sablon => $cilj) {
            if (strpos($sablon, '{') === false) {
                continue;
            }

            $sablonDelovi = explode('/', $sablon);
            if (count($sablonDelovi) !== count($delovi)) {
                continue;
            }

            $parametri = [];
            $poklapa   = true;

            foreach ($sablonDelovi as $i => $deo) {
                if (strlen($deo) > 1 && $deo[0] === '{') {
                    if ($delovi[$i] === '') {
                        $poklapa = false;
                        break;
                    }
                    $parametri[] = $delovi[$i];
                } elseif ($deo !== $delovi[$i]) {
                    $poklapa = false;
                    break;
                }
            }

            if ($poklapa) {
                return [
                    'kontroler' => $cilj[0],
                    'metoda'    => $cilj[1],
                    'parametri' => $parametri,
                ];
            }
        }

        return null;
    }
}
