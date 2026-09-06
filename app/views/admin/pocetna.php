<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="<?= url('admin/upiti?status=nov') ?>" class="admin-kartica akcenat">
            <span class="admin-kartica-ikona"><i class="bi bi-envelope-exclamation"></i></span>
            <span class="admin-kartica-broj"><?= (int) $brojNovih ?></span>
            <span class="admin-kartica-opis">novih upita</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('admin/upiti?status=u_obradi') ?>" class="admin-kartica">
            <span class="admin-kartica-ikona"><i class="bi bi-hourglass-split"></i></span>
            <span class="admin-kartica-broj"><?= (int) $brojUObradi ?></span>
            <span class="admin-kartica-opis">u obradi</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('admin/radovi') ?>" class="admin-kartica">
            <span class="admin-kartica-ikona"><i class="bi bi-images"></i></span>
            <span class="admin-kartica-broj"><?= (int) $brojRadova ?></span>
            <span class="admin-kartica-opis">radova u galeriji</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('admin/utisci') ?>" class="admin-kartica">
            <span class="admin-kartica-ikona"><i class="bi bi-chat-quote"></i></span>
            <span class="admin-kartica-broj"><?= (int) $brojUtisaka ?></span>
            <span class="admin-kartica-opis">utisaka klijenata</span>
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="admin-panel">
            <div class="admin-panel-vrh">
                <h2>Poslednji upiti</h2>
                <a href="<?= url('admin/upiti') ?>" class="veza-strelica">Svi upiti <i class="bi bi-arrow-right"></i></a>
            </div>

            <?php if (empty($poslednji)): ?>
                <p class="admin-prazno">Još uvek nema upita.</p>
            <?php else: ?>
                <div class="tabela-okvir">
                    <table class="admin-tabela">
                        <thead>
                            <tr><th>Klijent</th><th>Usluga</th><th>Datum</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($poslednji as $u): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($u['ime']) ?></strong>
                                        <small class="d-block text-muted"><?= e($u['email']) ?></small>
                                    </td>
                                    <td><?= e($u['usluga_naziv'] ?? '—') ?></td>
                                    <td><?= e(datum($u['kreiran'])) ?></td>
                                    <td><span class="stanje stanje-<?= e($u['status']) ?>"><?= e(str_replace('_', ' ', $u['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-panel">
            <div class="admin-panel-vrh">
                <h2>Upiti u poslednje dve nedelje</h2>
            </div>

            <?php
            $maks = 1;
            foreach ($poDanima as $d) {
                $maks = max($maks, (int) $d['broj']);
            }
            ?>

            <?php if (empty($poDanima)): ?>
                <p class="admin-prazno">Nema podataka za prikaz.</p>
            <?php else: ?>
                <div class="grafikon">
                    <?php foreach ($poDanima as $d): ?>
                        <div class="grafikon-stub" title="<?= e(datum($d['dan'])) ?>: <?= (int) $d['broj'] ?>">
                            <span class="grafikon-traka" style="height: <?= max(6, (int) round((int) $d['broj'] / $maks * 100)) ?>%">
                                <span class="grafikon-broj"><?= (int) $d['broj'] ?></span>
                            </span>
                            <span class="grafikon-oznaka"><?= date('d.m', strtotime($d['dan'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-panel mt-3">
            <div class="admin-panel-vrh">
                <h2>Brze radnje</h2>
            </div>
            <div class="brze-radnje">
                <a href="<?= url('admin/radovi/nov') ?>" class="dugme dugme-glavno w-100 mb-2">
                    <i class="bi bi-plus-lg"></i> Dodaj nov rad
                </a>
                <a href="<?= url('admin/usluge') ?>" class="dugme dugme-obrub-tamni w-100 mb-2">
                    <i class="bi bi-pencil"></i> Uredi usluge
                </a>
                <a href="<?= url('admin/utisci') ?>" class="dugme dugme-obrub-tamni w-100">
                    <i class="bi bi-check2-square"></i> Odobri utiske
                </a>
            </div>
        </div>
    </div>
</div>
