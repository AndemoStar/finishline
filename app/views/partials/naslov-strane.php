<?php
/**
 * Tamno zaglavlje unutrasnjih strana.
 * Ocekuje: $naslov, opciono $podnaslov i $putanjaStavke (niz [tekst => url]).
 */
?>
<section class="naslov-strane">
    <div class="container">
        <?php if (!empty($putanjaStavke)): ?>
            <ul class="putanja">
                <li><a href="<?= url() ?>">Početna</a></li>
                <?php foreach ($putanjaStavke as $tekst => $adresa): ?>
                    <li><?= $adresa ? '<a href="' . e($adresa) . '">' . e($tekst) . '</a>' : e($tekst) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h1><?= e($naslov) ?></h1>

        <?php if (!empty($podnaslov)): ?>
            <p><?= e($podnaslov) ?></p>
        <?php endif; ?>
    </div>
</section>
