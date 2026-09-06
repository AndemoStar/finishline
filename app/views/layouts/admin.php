<?php
/** Okvir administracije - bocni meni i gornja traka. */
$k = Auth::korisnik();
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($naslovStrane ?? 'Administracija') ?></title>
    <meta name="robots" content="noindex, nofollow">

    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="osnovna-adresa" content="<?= e(BASE_URL) ?>">

    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>?v=2" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>?v=2" rel="stylesheet">
</head>
<body class="telo-admina">

<div class="admin-omot">

    <aside class="admin-bocna" id="adminBocna">
        <a class="admin-logo" href="<?= url() ?>" title="Nazad na sajt">
            <strong>Finish<span>Line</span></strong>
            <small>administracija</small>
        </a>

        <nav class="admin-meni">
            <span class="admin-meni-naslov">Pregled</span>
            <a href="<?= url('admin') ?>" class="<?= aktivna('admin') && !aktivna('admin/upiti') && !aktivna('admin/radovi') && !aktivna('admin/usluge') && !aktivna('admin/utisci') ? 'aktivan' : '' ?>">
                <i class="bi bi-speedometer2"></i> Početna
            </a>

            <span class="admin-meni-naslov">Sadržaj</span>
            <a href="<?= url('admin/upiti') ?>" class="<?= aktivna('admin/upiti') ? 'aktivan' : '' ?>">
                <i class="bi bi-inbox"></i> Upiti
                <?php $novih = (new Upit())->brojPoStatusu('nov'); ?>
                <?php if ($novih > 0): ?><span class="admin-znacka"><?= $novih ?></span><?php endif; ?>
            </a>
            <a href="<?= url('admin/radovi') ?>" class="<?= aktivna('admin/radovi') ? 'aktivan' : '' ?>">
                <i class="bi bi-images"></i> Radovi
            </a>
            <a href="<?= url('admin/usluge') ?>" class="<?= aktivna('admin/usluge') ? 'aktivan' : '' ?>">
                <i class="bi bi-list-check"></i> Usluge
            </a>
            <a href="<?= url('admin/utisci') ?>" class="<?= aktivna('admin/utisci') ? 'aktivan' : '' ?>">
                <i class="bi bi-chat-quote"></i> Utisci
            </a>

            <span class="admin-meni-naslov">Sajt</span>
            <a href="<?= url() ?>" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Otvori sajt
            </a>
        </nav>

        <form method="post" action="<?= url('odjava') ?>" class="admin-odjava">
            <?= Csrf::polje() ?>
            <button type="submit"><i class="bi bi-box-arrow-left"></i> Odjava</button>
        </form>
    </aside>

    <div class="admin-glavni">
        <header class="admin-traka">
            <button class="admin-hamburger" id="adminHamburger" type="button" aria-label="Meni">
                <i class="bi bi-list"></i>
            </button>

            <h1><?= e(str_replace(' - administracija', '', $naslovStrane ?? 'Administracija')) ?></h1>

            <div class="admin-korisnik">
                <span class="admin-avatar"><?= e(mb_substr($k['ime'] ?? 'A', 0, 1, 'UTF-8')) ?></span>
                <div>
                    <strong><?= e($k['ime'] ?? '') ?></strong>
                    <small><?= e($k['uloga'] ?? '') ?></small>
                </div>
            </div>
        </header>

        <div class="admin-sadrzaj">
            <?php foreach (poruka() as $p): ?>
                <div class="poruka poruka-<?= e($p['tip']) ?>">
                    <i class="bi <?= $p['tip'] === 'uspeh' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                    <span><?= e($p['tekst']) ?></span>
                    <button type="button" class="poruka-zatvori" aria-label="Zatvori">&times;</button>
                </div>
            <?php endforeach; ?>

            <?= $sadrzaj ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>?v=2"></script>
<script src="<?= asset('js/admin.js') ?>?v=2"></script>
</body>
</html>
