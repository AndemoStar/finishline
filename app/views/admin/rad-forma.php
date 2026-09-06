<?php
$jeIzmena = $rad !== null;
$v = static function (string $polje, $podrazumevano = '') use ($rad) {
    if (isset($_SESSION['stari_unos'][$polje])) {
        return e($_SESSION['stari_unos'][$polje]);
    }
    return e($rad[$polje] ?? $podrazumevano);
};
?>

<form method="post" action="<?= url('admin/radovi/sacuvaj') ?>" class="admin-forma">
    <?= Csrf::polje() ?>
    <input type="hidden" name="id" value="<?= $jeIzmena ? (int) $rad['id'] : '' ?>">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="admin-panel">
                <div class="admin-panel-vrh">
                    <h2><?= $jeIzmena ? 'Izmena rada' : 'Nov rad' ?></h2>
                    <a href="<?= url('admin/radovi') ?>" class="veza-strelica">
                        <i class="bi bi-arrow-left"></i> Nazad na spisak
                    </a>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="oznaka" for="naziv">Naziv rada <span>*</span></label>
                        <input type="text" class="polje <?= isset($greske['naziv']) ? 'polje-greska' : '' ?>"
                               id="naziv" name="naziv" value="<?= $v('naziv') ?>" required maxlength="160"
                               placeholder="npr. Stan u novogradnji, Grbavica">
                        <small class="greska-polja"><?= e($greske['naziv'] ?? '') ?></small>
                    </div>

                    <div class="col-md-6">
                        <label class="oznaka" for="usluga_id">Usluga</label>
                        <select class="polje" id="usluga_id" name="usluga_id">
                            <option value="">— bez usluge —</option>
                            <?php foreach ($usluge as $u): ?>
                                <option value="<?= (int) $u['id'] ?>"
                                    <?= (string) ($rad['usluga_id'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['naziv']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="oznaka" for="lokacija">Lokacija</label>
                        <input type="text" class="polje" id="lokacija" name="lokacija"
                               value="<?= $v('lokacija') ?>" maxlength="120" placeholder="npr. Novi Sad">
                    </div>

                    <div class="col-md-4">
                        <label class="oznaka" for="kvadratura">Kvadratura (m&sup2;)</label>
                        <input type="number" class="polje" id="kvadratura" name="kvadratura"
                               value="<?= $v('kvadratura') ?>" min="0" max="100000">
                    </div>

                    <div class="col-md-4">
                        <label class="oznaka" for="trajanje_dana">Trajanje (dana)</label>
                        <input type="number" class="polje" id="trajanje_dana" name="trajanje_dana"
                               value="<?= $v('trajanje_dana') ?>" min="0" max="999">
                    </div>

                    <div class="col-md-4">
                        <label class="oznaka" for="godina">Godina</label>
                        <input type="number" class="polje <?= isset($greske['godina']) ? 'polje-greska' : '' ?>"
                               id="godina" name="godina" value="<?= $v('godina', (string) date('Y')) ?>"
                               min="2000" max="<?= (int) date('Y') + 1 ?>">
                        <small class="greska-polja"><?= e($greske['godina'] ?? '') ?></small>
                    </div>

                    <div class="col-12">
                        <label class="oznaka" for="opis">Opis posla <span>*</span></label>
                        <textarea class="polje <?= isset($greske['opis']) ? 'polje-greska' : '' ?>"
                                  id="opis" name="opis" rows="6" required
                                  placeholder="Šta je urađeno, u kom obimu, na koji način..."><?= $v('opis') ?></textarea>
                        <small class="greska-polja"><?= e($greske['opis'] ?? '') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-panel">
                <div class="admin-panel-vrh"><h2>Objavljivanje</h2></div>

                <label class="prekidac">
                    <input type="checkbox" name="objavljen" value="1"
                        <?= !$jeIzmena || (int) $rad['objavljen'] === 1 ? 'checked' : '' ?>>
                    <span class="prekidac-telo"></span>
                    <span>Vidljivo na sajtu</span>
                </label>

                <label class="prekidac">
                    <input type="checkbox" name="izdvojen" value="1"
                        <?= $jeIzmena && (int) $rad['izdvojen'] === 1 ? 'checked' : '' ?>>
                    <span class="prekidac-telo"></span>
                    <span>Izdvojeno na početnoj</span>
                </label>

                <button type="submit" class="dugme dugme-glavno w-100 mt-3">
                    <i class="bi bi-check-lg"></i> Sačuvaj rad
                </button>
            </div>

            <!-- ============ OTPREMANJE GLAVNE SLIKE PREKO WEB SERVISA ============ -->
            <div class="admin-panel mt-3">
                <div class="admin-panel-vrh"><h2>Naslovna slika</h2></div>

                <input type="hidden" name="slika" id="putanjaSlike" value="<?= $v('slika') ?>">

                <div class="zona-otpremanja" id="zonaNaslovne" data-grupa="radovi">
                    <input type="file" id="poljeNaslovne" accept="image/jpeg,image/png,image/webp,image/gif" hidden>

                    <div class="zona-pregled" id="pregledNaslovne" <?= $v('slika') === '' ? 'hidden' : '' ?>>
                        <img src="<?= slika($rad['slika'] ?? null) ?>" alt="Naslovna slika" id="slikaNaslovne">
                        <button type="button" class="zona-ukloni" id="ukloniNaslovnu" title="Ukloni">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="zona-prazna" id="praznaNaslovne" <?= $v('slika') !== '' ? 'hidden' : '' ?>>
                        <i class="bi bi-cloud-arrow-up"></i>
                        <strong>Prevucite sliku ovde</strong>
                        <span>ili kliknite da izaberete fajl</span>
                        <small>JPG, PNG, WEBP ili GIF &middot; do 3 MB</small>
                    </div>

                    <div class="zona-napredak" id="napredakNaslovne" hidden>
                        <div class="traka-napretka"><span></span></div>
                        <small>Otpremam...</small>
                    </div>
                </div>
            </div>

            <?php if ($jeIzmena): ?>
                <!-- ============ DODATNE FOTOGRAFIJE ============ -->
                <div class="admin-panel mt-3">
                    <div class="admin-panel-vrh">
                        <h2>Dodatne fotografije</h2>
                        <span class="admin-brojka" id="brojFotografija"><?= count($slike) ?></span>
                    </div>

                    <div class="mreza-slika" id="mrezaSlika" data-rad="<?= (int) $rad['id'] ?>">
                        <?php foreach ($slike as $s): ?>
                            <figure class="stavka-slike" data-slika="<?= (int) $s['id'] ?>">
                                <img src="<?= slika($s['putanja']) ?>" alt="<?= e($s['opis']) ?>">
                                <button type="button" class="zona-ukloni" data-obrisi-sliku="<?= (int) $s['id'] ?>" title="Obriši">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </figure>
                        <?php endforeach; ?>
                    </div>

                    <div class="zona-otpremanja mala" id="zonaDodatnih" data-grupa="radovi" data-rad="<?= (int) $rad['id'] ?>">
                        <input type="file" id="poljeDodatnih" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
                        <div class="zona-prazna">
                            <i class="bi bi-images"></i>
                            <strong>Dodaj fotografije</strong>
                            <span>može i više odjednom</span>
                        </div>
                        <div class="zona-napredak" id="napredakDodatnih" hidden>
                            <div class="traka-napretka"><span></span></div>
                            <small>Otpremam...</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="admin-panel mt-3">
                    <p class="admin-prazno mb-0">
                        <i class="bi bi-info-circle"></i>
                        Dodatne fotografije možete dodati posle prvog čuvanja rada.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php unset($_SESSION['stari_unos']); ?>
