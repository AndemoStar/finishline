<?php
$naslov        = 'Usluge i cenovnik';
$podnaslov     = 'Cene su okvirne — tačan iznos dobijate posle izlaska na teren.';
$putanjaStavke = ['Usluge' => null];
require APP_ROOT . '/app/views/partials/naslov-strane.php';
?>

<section class="odeljak">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($usluge as $u): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require APP_ROOT . '/app/views/partials/kartica-usluge.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="odeljak odeljak-sivi">
    <div class="container">
        <div class="zaglavlje-odeljka">
            <div>
                <span class="nadnaslov">Cenovnik</span>
                <h2>Pregled cena po jedinici mere</h2>
            </div>
            <p class="zaglavlje-opis">
                U cenu je uračunata radna snaga i alat. Materijal se obračunava posebno,
                po nabavnim cenama.
            </p>
        </div>

        <div class="tabela-okvir">
            <table class="tabela-cena">
                <thead>
                    <tr>
                        <th>Usluga</th>
                        <th>Opis</th>
                        <th class="desno">Cena od</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usluge as $u): ?>
                        <tr>
                            <td><a href="<?= url('usluge/' . $u['slug']) ?>"><?= e($u['naziv']) ?></a></td>
                            <td class="blagi"><?= e($u['kratak_opis']) ?></td>
                            <td class="desno">
                                <strong><?= e(cena($u['cena_od'])) ?></strong>
                                <span class="blagi">/ <?= e($u['jedinica']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="napomena-tabele">
            Za stanove veće od 100 m&sup2; i za radove u kontinuitetu odobravamo popust.
            <a href="<?= url('kontakt') ?>#forma">Pošaljite upit</a> za tačnu ponudu.
        </p>
    </div>
</section>
