<?php $uslugeFooter = $uslugeFooter ?? (new Usluga())->aktivne(6); ?>

<section class="traka-poziv">
    <div class="container">
        <div class="traka-poziv-sadrzaj">
            <div>
                <h2>Treba vam procena?</h2>
                <p>Izađemo, izmerimo i pošaljemo ponudu u roku od 24 sata.</p>
            </div>
            <div class="traka-poziv-dugmad">
                <a href="tel:<?= e(str_replace(' ', '', FIRMA_TEL)) ?>" class="dugme dugme-belo">
                    <?= e(FIRMA_TEL) ?>
                </a>
                <a href="<?= url('kontakt') ?>#forma" class="dugme dugme-obrub">Pošalji upit</a>
            </div>
        </div>
    </div>
</section>

<footer class="podnozje">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <a class="logo" href="<?= url() ?>">
                    <strong>Finish<span>Line</span></strong>
                    <small>završni radovi</small>
                </a>
                <p class="podnozje-opis">
                    Gletovanje, krečenje, gips i dekorativne obrade.
                    Novi Sad i okolina.
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <h3>Sajt</h3>
                <ul class="podnozje-lista">
                    <li><a href="<?= url() ?>">Početna</a></li>
                    <li><a href="<?= url('usluge') ?>">Usluge</a></li>
                    <li><a href="<?= url('radovi') ?>">Radovi</a></li>
                    <li><a href="<?= url('o-nama') ?>">O nama</a></li>
                    <li><a href="<?= url('kontakt') ?>">Kontakt</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h3>Usluge</h3>
                <ul class="podnozje-lista">
                    <?php foreach ($uslugeFooter as $u): ?>
                        <li><a href="<?= url('usluge/' . $u['slug']) ?>"><?= e($u['naziv']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-3">
                <h3>Kontakt</h3>
                <ul class="podnozje-kontakt">
                    <li><a href="tel:<?= e(str_replace(' ', '', FIRMA_TEL)) ?>"><?= e(FIRMA_TEL) ?></a></li>
                    <li><a href="mailto:<?= e(FIRMA_MAIL) ?>"><?= e(FIRMA_MAIL) ?></a></li>
                    <li><?= e(FIRMA_ADRESA) ?></li>
                </ul>
            </div>
        </div>

        <div class="podnozje-dno">
            <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>.</p>
            <p>
                <?php if (Auth::prijavljen()): ?>
                    <a href="<?= url('admin') ?>">Administracija</a>
                <?php else: ?>
                    <a href="<?= url('prijava') ?>">Prijava</a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>
