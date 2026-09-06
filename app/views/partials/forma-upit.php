<?php
/**
 * Forma za slanje upita.
 *
 * Salje se Ajax-om na web servis POST api/upiti. Ako je JavaScript
 * iskljucen, forma se normalno salje na kontakt/posalji i radi isto.
 *
 * Ocekuje: $usluge, opciono $greske
 */
$greske = $greske ?? [];
?>
<form id="formaUpit" class="forma-upit" method="post" action="<?= url('kontakt/posalji') ?>" novalidate>
    <?= Csrf::polje() ?>

    <!-- Polje koje ljudi ne vide; ako ga bot popuni, upit se odbija -->
    <div class="polje-zamka" aria-hidden="true">
        <label for="website">Ne popunjavajte ovo polje</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="oznaka" for="ime">Ime i prezime <span>*</span></label>
            <input type="text" class="polje <?= isset($greske['ime']) ? 'polje-greska' : '' ?>"
                   id="ime" name="ime" value="<?= staro('ime') ?>" required maxlength="100"
                   placeholder="npr. Marko Marković">
            <small class="greska-polja" data-greska="ime"><?= e($greske['ime'] ?? '') ?></small>
        </div>

        <div class="col-md-6">
            <label class="oznaka" for="email">Email <span>*</span></label>
            <input type="email" class="polje <?= isset($greske['email']) ? 'polje-greska' : '' ?>"
                   id="email" name="email" value="<?= staro('email') ?>" required maxlength="150"
                   placeholder="vas@email.com">
            <small class="greska-polja" data-greska="email"><?= e($greske['email'] ?? '') ?></small>
        </div>

        <div class="col-md-6">
            <label class="oznaka" for="telefon">Telefon</label>
            <input type="tel" class="polje <?= isset($greske['telefon']) ? 'polje-greska' : '' ?>"
                   id="telefon" name="telefon" value="<?= staro('telefon') ?>" maxlength="40"
                   placeholder="064 123 4567">
            <small class="greska-polja" data-greska="telefon"><?= e($greske['telefon'] ?? '') ?></small>
        </div>

        <div class="col-md-6">
            <label class="oznaka" for="usluga_id">Usluga</label>
            <select class="polje" id="usluga_id" name="usluga_id">
                <option value="">Izaberite uslugu</option>
                <?php foreach ($usluge as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= staro('usluga_id') === (string) $u['id'] ? 'selected' : '' ?>>
                        <?= e($u['naziv']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="oznaka" for="kvadratura">Kvadratura (m&sup2;)</label>
            <input type="number" class="polje <?= isset($greske['kvadratura']) ? 'polje-greska' : '' ?>"
                   id="kvadratura" name="kvadratura" value="<?= staro('kvadratura') ?>"
                   min="1" max="100000" placeholder="npr. 65">
            <small class="greska-polja" data-greska="kvadratura"><?= e($greske['kvadratura'] ?? '') ?></small>
        </div>

        <div class="col-12">
            <label class="oznaka" for="poruka">Opis posla <span>*</span></label>
            <textarea class="polje <?= isset($greske['poruka']) ? 'polje-greska' : '' ?>"
                      id="poruka" name="poruka" rows="5" required maxlength="2000"
                      placeholder="Ukratko opišite šta vam treba — vrsta prostora, stanje zidova, rokovi..."><?= staro('poruka') ?></textarea>
            <div class="polje-dno">
                <small class="greska-polja" data-greska="poruka"><?= e($greske['poruka'] ?? '') ?></small>
                <small class="brojac-znakova"><span id="brojacPoruke">0</span> / 2000</small>
            </div>
        </div>

        <div class="col-12">
            <div class="forma-dno">
                <button type="submit" class="dugme dugme-glavno dugme-veliko" id="dugmeSlanje">
                    <span class="dugme-tekst">Pošalji upit</span>
                    <span class="dugme-ucitavanje" hidden>
                        <span class="spinner"></span> Šaljem...
                    </span>
                </button>
                <p class="forma-napomena">
                    <i class="bi bi-shield-check"></i>
                    Odgovaramo u roku od 24 sata. Vaši podaci se ne prosleđuju trećim licima.
                </p>
            </div>
        </div>
    </div>

    <!-- Odgovor web servisa ispisuje JavaScript -->
    <div class="forma-odgovor" id="formaOdgovor" hidden></div>
</form>
