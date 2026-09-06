<?php /* Okvir bez menija i podnozja - koristi ga strana za prijavu. */ ?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($naslovStrane ?? APP_NAME) ?></title>
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
</head>
<body class="telo-prijave">

<?= $sadrzaj ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
