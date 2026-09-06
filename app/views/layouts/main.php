<?php
/**
 * Glavni okvir javnog dela sajta.
 * U promenljivoj $sadrzaj se nalazi vec iscrtan prikaz.
 */
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($naslovStrane ?? APP_NAME . ' - ' . APP_SLOGAN) ?></title>
    <meta name="description" content="<?= e($opisStrane ?? 'Gletovanje, krečenje, gips i dekorativni malteri. Novi Sad i okolina.') ?>">

    <!-- CSRF token koji JavaScript salje uz svaki Ajax zahtev -->
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <meta name="osnovna-adresa" content="<?= e(BASE_URL) ?>">

    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>?v=2" rel="stylesheet">
</head>
<body>

<a class="preskoci" href="#glavni">Pređi na sadržaj</a>

<?php require APP_ROOT . '/app/views/partials/zaglavlje.php'; ?>

<main id="glavni">
    <?php require APP_ROOT . '/app/views/partials/poruke.php'; ?>
    <?= $sadrzaj ?>
</main>

<?php require APP_ROOT . '/app/views/partials/podnozje.php'; ?>

<!-- Uvecana slika iz galerije (otvara je JavaScript) -->
<div class="svetlo-boks" id="svetloBoks" hidden>
    <button class="svetlo-zatvori" type="button" aria-label="Zatvori">&times;</button>
    <button class="svetlo-strelica levo"  type="button" aria-label="Prethodna">&#10094;</button>
    <img src="" alt="" id="svetloSlika">
    <button class="svetlo-strelica desno" type="button" aria-label="Sledeća">&#10095;</button>
    <p class="svetlo-opis" id="svetloOpis"></p>
</div>

<button class="na-vrh" id="naVrh" type="button" aria-label="Na vrh strane">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>?v=2"></script>
<?php foreach (($skripte ?? []) as $skripta): ?>
    <script src="<?= asset('js/' . $skripta) ?>?v=2"></script>
<?php endforeach; ?>

</body>
</html>
