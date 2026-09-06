<?php
/* Jednokratne poruke (uspeh / greska) posle preusmeravanja */
$sve = poruka();

if (!empty($sve)):
    ?>
    <div class="container poruke-okvir">
        <?php foreach ($sve as $p): ?>
            <div class="poruka poruka-<?= e($p['tip']) ?>">
                <i class="bi <?= $p['tip'] === 'uspeh' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                <span><?= e($p['tekst']) ?></span>
                <button type="button" class="poruka-zatvori" aria-label="Zatvori">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
