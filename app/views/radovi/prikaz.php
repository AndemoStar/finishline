<?php
$naslov        = $rad['naziv'];
$podnaslov     = null;
$putanjaStavke = ['Radovi' => url('radovi'), $rad['naziv'] => null];
require APP_ROOT . '/app/views/partials/naslov-strane.php';
?>

<section class="odeljak">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <!-- Klik na sliku otvara uvecani prikaz (JavaScript) -->
                <div class="slika-okvir mb-4" data-galerija>
                    <a href="#" data-uvecaj="<?= slika($rad['slika']) ?>" data-opis="<?= e($rad['naziv']) ?>">
                        <img src="<?= slika($rad['slika']) ?>" alt="<?= e($rad['naziv']) ?>">
                    </a>

                    <?php if (!empty($slike)): ?>
                        <div class="row g-3 mt-1">
                            <?php foreach ($slike as $s): ?>
                                <div class="col-4 col-md-3">
                                    <a href="#" class="mala-slika"
                                       data-uvecaj="<?= slika($s['putanja']) ?>"
                                       data-opis="<?= e($s['opis'] ?: $rad['naziv']) ?>">
                                        <img src="<?= slika($s['putanja']) ?>" alt="<?= e($s['opis'] ?: $rad['naziv']) ?>" loading="lazy">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <span class="nadnaslov">Opis posla</span>
                <h2><?= e($rad['naziv']) ?></h2>

                <div class="tekst-sadrzaja">
                    <?php foreach (preg_split('/\r\n|\r|\n/', (string) $rad['opis']) as $pasus): ?>
                        <?php if (trim($pasus) !== ''): ?>
                            <p><?= e($pasus) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="bocna-kutija">
                    <h3>Podaci o radu</h3>
                    <ul class="podaci-lista">
                        <?php if (!empty($rad['usluga_naziv'])): ?>
                            <li>
                                <span>Usluga</span>
                                <strong><a href="<?= url('usluge/' . $rad['usluga_slug']) ?>"><?= e($rad['usluga_naziv']) ?></a></strong>
                            </li>
                        <?php endif; ?>
                        <?php if (!empty($rad['lokacija'])): ?>
                            <li><span>Lokacija</span><strong><?= e($rad['lokacija']) ?></strong></li>
                        <?php endif; ?>
                        <?php if (!empty($rad['kvadratura'])): ?>
                            <li><span>Površina</span><strong><?= (int) $rad['kvadratura'] ?> m&sup2;</strong></li>
                        <?php endif; ?>
                        <?php if (!empty($rad['trajanje_dana'])): ?>
                            <li><span>Trajanje</span><strong><?= (int) $rad['trajanje_dana'] ?> dana</strong></li>
                        <?php endif; ?>
                        <?php if (!empty($rad['godina'])): ?>
                            <li><span>Godina</span><strong><?= (int) $rad['godina'] ?>.</strong></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="bocna-kutija istaknuta">
                    <span class="bocna-oznaka">Sličan posao?</span>
                    <p>Pošaljite nam osnovne podatke o prostoru i dobićete ponudu u roku od 24 sata.</p>
                    <a href="<?= url('kontakt') ?>#forma" class="dugme dugme-glavno w-100">
                        Zatraži ponudu <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </aside>
        </div>

        <?php if (count($slicni) > 1): ?>
            <div class="mt-5 pt-4">
                <div class="zaglavlje-odeljka">
                    <div>
                        <span class="nadnaslov">Slični radovi</span>
                        <h2>Još iz iste kategorije</h2>
                    </div>
                    <a href="<?= url('radovi') ?>" class="dugme dugme-obrub-tamni">Svi radovi <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="row g-4">
                    <?php foreach ($slicni as $r): ?>
                        <?php if ((int) $r['id'] === (int) $rad['id']) { continue; } ?>
                        <div class="col-md-6 col-lg-4">
                            <?php require APP_ROOT . '/app/views/partials/kartica-rada.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
