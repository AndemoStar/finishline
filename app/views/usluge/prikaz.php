<?php
$naslov        = $usluga['naziv'];
$podnaslov     = $usluga['kratak_opis'];
$putanjaStavke = ['Usluge' => url('usluge'), $usluga['naziv'] => null];
require APP_ROOT . '/app/views/partials/naslov-strane.php';
?>

<section class="odeljak">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="slika-okvir mb-4">
                    <img src="<?= slika($usluga['slika']) ?>" alt="<?= e($usluga['naziv']) ?>">
                </div>

                <div class="tekst-sadrzaja">
                    <?php foreach (preg_split('/\r\n|\r|\n/', (string) $usluga['opis']) as $pasus): ?>
                        <?php if (trim($pasus) !== ''): ?>
                            <p><?= e($pasus) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($radovi)): ?>
                    <h2 class="mt-5 mb-4">Primeri iz prakse</h2>
                    <div class="row g-4">
                        <?php foreach ($radovi as $r): ?>
                            <div class="col-sm-6">
                                <?php require APP_ROOT . '/app/views/partials/kartica-rada.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="col-lg-4">
                <div class="bocna-kutija istaknuta">
                    <h3>Cena</h3>
                    <div class="bocna-cena">
                        <strong><?= e(cena($usluga['cena_od'])) ?></strong>
                        od, po <?= e($usluga['jedinica']) ?>
                    </div>
                    <p>Cena obuhvata rad i alat. Materijal se obračunava posebno.</p>
                    <a href="<?= url('kontakt') ?>#forma" class="dugme dugme-glavno w-100">Zatraži ponudu</a>
                </div>

                <div class="bocna-kutija">
                    <h3>Ostale usluge</h3>
                    <ul class="bocna-lista">
                        <?php foreach ($ostale as $o): ?>
                            <li class="<?= (int) $o['id'] === (int) $usluga['id'] ? 'aktivna' : '' ?>">
                                <a href="<?= url('usluge/' . $o['slug']) ?>"><?= e($o['naziv']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>
