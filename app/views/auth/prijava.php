<div class="prijava-omot">
    <div class="prijava-kutija">
        <a class="logo prijava-logo" href="<?= url() ?>">
            <strong>Finish<span>Line</span></strong>
            <small>završni radovi</small>
        </a>

        <h1>Prijava na administraciju</h1>
        <p class="prijava-uvod">Pristup je dozvoljen samo ovlašćenim korisnicima.</p>

        <?php foreach (poruka() as $p): ?>
            <div class="poruka poruka-<?= e($p['tip']) ?>">
                <i class="bi <?= $p['tip'] === 'uspeh' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                <span><?= e($p['tekst']) ?></span>
            </div>
        <?php endforeach; ?>

        <?php if ($greska !== null): ?>
            <div class="poruka poruka-greska">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= e($greska) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('prijava') ?>" autocomplete="on">
            <?= Csrf::polje() ?>

            <div class="mb-3">
                <label class="oznaka" for="email">Email adresa</label>
                <div class="polje-sa-ikonom">
                    <i class="bi bi-envelope"></i>
                    <input type="email" class="polje" id="email" name="email"
                           value="<?= e($email) ?>" required autofocus
                           autocomplete="username" placeholder="admin@finishline.rs">
                </div>
            </div>

            <div class="mb-4">
                <label class="oznaka" for="lozinka">Lozinka</label>
                <div class="polje-sa-ikonom">
                    <i class="bi bi-lock"></i>
                    <input type="password" class="polje" id="lozinka" name="lozinka"
                           required autocomplete="current-password" placeholder="Vaša lozinka">
                    <button type="button" class="prikazi-lozinku" id="prikaziLozinku" aria-label="Prikaži lozinku">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="dugme dugme-glavno w-100 dugme-veliko">
                Prijavi se <i class="bi bi-box-arrow-in-right"></i>
            </button>
        </form>

        <div class="prijava-demo">
            <strong>Demo nalozi za testiranje</strong>
            <div class="demo-red">
                <span>Administrator</span>
                <code>admin@finishline.rs</code>
                <code>Demo1234</code>
            </div>
            <div class="demo-red">
                <span>Urednik</span>
                <code>urednik@finishline.rs</code>
                <code>Demo1234</code>
            </div>
            <small>Urednik nema pristup administraciji — služi za proveru kontrole prava.</small>
        </div>

        <a href="<?= url() ?>" class="prijava-nazad">
            <i class="bi bi-arrow-left"></i> Nazad na sajt
        </a>
    </div>
</div>

<script>
    // Prikaz i sakrivanje lozinke
    document.getElementById('prikaziLozinku').addEventListener('click', function () {
        const polje = document.getElementById('lozinka');
        const ikona = this.querySelector('i');
        const tekst = polje.type === 'password';

        polje.type = tekst ? 'text' : 'password';
        ikona.className = tekst ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
