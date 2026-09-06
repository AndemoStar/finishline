<?php /* Kartica jednog rada u galeriji. Ocekuje $r. */ ?>
<article class="kartica-rad">
    <a class="kartica-rad-slika" href="<?= url('radovi/' . (int) $r['id']) ?>">
        <img src="<?= slika($r['slika']) ?>" alt="<?= e($r['naziv']) ?>" loading="lazy">
    </a>
    <div class="kartica-rad-telo">
        <h3><a href="<?= url('radovi/' . (int) $r['id']) ?>"><?= e($r['naziv']) ?></a></h3>
        <ul class="kartica-rad-meta">
            <?php if (!empty($r['usluga_naziv'])): ?><li><?= e($r['usluga_naziv']) ?></li><?php endif; ?>
            <?php if (!empty($r['lokacija'])): ?><li><?= e($r['lokacija']) ?></li><?php endif; ?>
            <?php if (!empty($r['kvadratura'])): ?><li><?= (int) $r['kvadratura'] ?> m&sup2;</li><?php endif; ?>
        </ul>
    </div>
</article>
