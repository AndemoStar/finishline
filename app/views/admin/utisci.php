<div class="admin-panel">
    <div class="admin-panel-vrh">
        <h2>Utisci klijenata</h2>
        <span class="admin-napomena">Na sajtu se prikazuju samo odobreni utisci</span>
    </div>

    <?php if (empty($utisci)): ?>
        <p class="admin-prazno">Nema unetih utisaka.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($utisci as $u): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="kartica-utisak-admin <?= (int) $u['odobren'] === 1 ? '' : 'ceka' ?>">
                        <div class="utisak-admin-vrh">
                            <span class="stanje <?= (int) $u['odobren'] === 1 ? 'stanje-zavrsen' : 'stanje-nov' ?>">
                                <?= (int) $u['odobren'] === 1 ? 'objavljen' : 'čeka odobrenje' ?>
                            </span>
                        </div>

                        <p class="utisak-tekst"><?= e($u['tekst']) ?></p>

                        <div class="utisak-autor">
                            <span class="utisak-inicijal"><?= e(mb_substr($u['ime'], 0, 1, 'UTF-8')) ?></span>
                            <div>
                                <strong><?= e($u['ime']) ?></strong>
                                <small><?= e($u['lokacija']) ?> &middot; <?= e(datum($u['kreiran'])) ?></small>
                            </div>
                        </div>

                        <div class="utisak-radnje">
                            <form method="post" action="<?= url('admin/utisci') ?>">
                                <?= Csrf::polje() ?>
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <?php if ((int) $u['odobren'] === 1): ?>
                                    <button type="submit" name="radnja" value="skloni" class="dugme dugme-obrub-tamni">
                                        <i class="bi bi-eye-slash"></i> Skloni
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="radnja" value="odobri" class="dugme dugme-glavno">
                                        <i class="bi bi-check-lg"></i> Odobri
                                    </button>
                                <?php endif; ?>
                            </form>

                            <form method="post" action="<?= url('admin/utisci') ?>"
                                  onsubmit="return confirm('Obrisati ovaj utisak? Radnja se ne može poništiti.');">
                                <?= Csrf::polje() ?>
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button type="submit" name="radnja" value="obrisi" class="ikona-dugme opasno" title="Obriši">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
