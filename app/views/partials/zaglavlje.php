<?php /* Glavni meni */ ?>
<header class="zaglavlje" id="zaglavlje">
    <div class="container">
        <nav class="navbar navbar-expand-lg">
            <a class="logo" href="<?= url() ?>">
                <strong>Finish<span>Line</span></strong>
                <small>završni radovi</small>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#glavniMeni"
                    aria-controls="glavniMeni" aria-expanded="false" aria-label="Otvori meni">
                <span class="crta"></span><span class="crta"></span><span class="crta"></span>
            </button>

            <div class="collapse navbar-collapse" id="glavniMeni">
                <ul class="navbar-nav ms-auto meni">
                    <li class="nav-item"><a class="nav-link <?= aktivna('') ? 'aktivan' : '' ?>" href="<?= url() ?>">Početna</a></li>
                    <li class="nav-item"><a class="nav-link <?= aktivna('usluge') ? 'aktivan' : '' ?>" href="<?= url('usluge') ?>">Usluge</a></li>
                    <li class="nav-item"><a class="nav-link <?= aktivna('radovi') ? 'aktivan' : '' ?>" href="<?= url('radovi') ?>">Radovi</a></li>
                    <li class="nav-item"><a class="nav-link <?= aktivna('o-nama') ? 'aktivan' : '' ?>" href="<?= url('o-nama') ?>">O nama</a></li>
                    <li class="nav-item"><a class="nav-link <?= aktivna('kontakt') ? 'aktivan' : '' ?>" href="<?= url('kontakt') ?>">Kontakt</a></li>
                </ul>

                <div class="zaglavlje-desno">
                    <a class="zaglavlje-telefon" href="tel:<?= e(str_replace(' ', '', FIRMA_TEL)) ?>">
                        <i class="bi bi-telephone"></i><?= e(FIRMA_TEL) ?>
                    </a>
                    <a href="<?= url('kontakt') ?>#forma" class="dugme dugme-glavno">Zatraži ponudu</a>
                </div>
            </div>
        </nav>
    </div>
</header>
