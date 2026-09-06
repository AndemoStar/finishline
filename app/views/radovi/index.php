<?php
$skripte       = ['galerija.js'];
$naslov        = 'Izvedeni radovi';
$podnaslov     = 'Fotografije sa gradilišta i gotovih prostora. Filtrirajte po usluzi koja vas zanima.';
$putanjaStavke = ['Radovi' => null];
require APP_ROOT . '/app/views/partials/naslov-strane.php';
?>

<section class="odeljak">
    <div class="container">

        <!-- Filteri: klik menja sadrzaj preko web servisa, bez osvezavanja strane -->
        <div class="galerija-vrh">
            <div class="filteri">
                <button type="button" class="filter <?= $izabrana === null ? 'aktivan' : '' ?>" data-usluga="">
                    Sve
                </button>
                <?php foreach ($usluge as $u): ?>
                    <button type="button"
                            class="filter <?= $izabrana === (int) $u['id'] ? 'aktivan' : '' ?>"
                            data-usluga="<?= (int) $u['id'] ?>">
                        <?= e($u['naziv']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <span class="brojac-rezultata" id="brojRezultata">
                Prikazano <?= count($radovi) ?> od <?= (int) $ukupno ?>
            </span>
        </div>

        <div class="row g-4" id="spisakRadova"
             data-po-strani="<?= (int) $poStrani ?>"
             data-ucitano="<?= count($radovi) ?>"
             data-ukupno="<?= (int) $ukupno ?>"
             data-usluga="<?= $izabrana !== null ? (int) $izabrana : '' ?>">
            <?php foreach ($radovi as $r): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require APP_ROOT . '/app/views/partials/kartica-rada.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="galerija-stanje" id="stanjeGalerije" <?= $ukupno > 0 ? 'hidden' : '' ?>>
            <i class="bi bi-images"></i>
            <p>Za izabranu uslugu još uvek nema objavljenih radova.</p>
        </div>

        <div class="text-center mt-5">
            <button type="button" class="dugme dugme-obrub-tamni dugme-veliko" id="dugmeJos"
                    <?= count($radovi) >= $ukupno ? 'hidden' : '' ?>>
                <span class="dugme-tekst">Učitaj još radova</span>
                <span class="dugme-ucitavanje" hidden><span class="spinner"></span> Učitavam...</span>
            </button>
        </div>
    </div>
</section>
