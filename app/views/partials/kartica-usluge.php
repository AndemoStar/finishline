<?php /* Kartica jedne usluge. Ocekuje $u. */ ?>
<article class="kartica-usluga">
    <a class="kartica-usluga-slika" href="<?= url('usluge/' . $u['slug']) ?>">
        <img src="<?= slika($u['slika']) ?>" alt="<?= e($u['naziv']) ?>" loading="lazy">
    </a>
    <div class="kartica-usluga-telo">
        <h3><a href="<?= url('usluge/' . $u['slug']) ?>"><?= e($u['naziv']) ?></a></h3>
        <p><?= e($u['kratak_opis']) ?></p>
        <div class="kartica-usluga-dno">
            <span class="kartica-cena">od <strong><?= e(cena($u['cena_od'])) ?></strong> / <?= e($u['jedinica']) ?></span>
            <a href="<?= url('usluge/' . $u['slug']) ?>" class="veza-strelica">Detaljnije <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</article>
