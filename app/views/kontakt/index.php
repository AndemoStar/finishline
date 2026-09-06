<?php
$skripte       = ['upit.js'];
$naslov        = 'Kontakt i besplatna procena';
$podnaslov     = 'Pozovite nas ili popunite formu — javljamo se u roku od 24 sata. Izlazak na teren i procena se ne naplaćuju.';
$putanjaStavke = ['Kontakt' => null];
require APP_ROOT . '/app/views/partials/naslov-strane.php';
?>

<section class="odeljak">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="nadnaslov">Podaci</span>
                <h2>Tu smo za sva pitanja</h2>
                <p class="uvodni-tekst">
                    Za manje popravke ponudu možemo dati i na osnovu fotografija.
                    Za veće poslove izlazimo na teren istog ili sledećeg dana.
                </p>

                <ul class="kontakt-lista">
                    <li>
                        <i class="bi bi-telephone"></i>
                        <div>
                            <small>Telefon</small>
                            <a href="tel:<?= e(str_replace(' ', '', FIRMA_TEL)) ?>"><?= e(FIRMA_TEL) ?></a>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-envelope"></i>
                        <div>
                            <small>Email</small>
                            <a href="mailto:<?= e(FIRMA_MAIL) ?>"><?= e(FIRMA_MAIL) ?></a>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <small>Adresa</small>
                            <span><?= e(FIRMA_ADRESA) ?></span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-lg-7">
                <div class="okvir-forme" id="forma">
                    <span class="nadnaslov">Upit</span>
                    <h2>Pošaljite zahtev za ponudu</h2>
                    <p class="uvodni-tekst">
                        Polja označena zvezdicom su obavezna. Što više detalja navedete,
                        to je procena preciznija.
                    </p>

                    <?php require APP_ROOT . '/app/views/partials/forma-upit.php'; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php unset($_SESSION['stari_unos']); ?>
