<?php $skripte = ['pocetna.js', 'upit.js']; ?>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="hero-pozadina" style="background-image:url('<?= asset('img/hero.jpg') ?>')"></div>
    <div class="hero-preliv"></div>

    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h1>Završni radovi, urađeni kako treba</h1>
                <p class="hero-uvod">
                    Gletovanje, krečenje, gips i dekorativne obrade za stanove, kuće i
                    poslovne prostore na teritoriji Srbije. Fiksna cena iz ponude i čist prostor
                    posle radova.
                </p>
                <div class="hero-dugmad">
                    <a href="<?= url('kontakt') ?>#forma" class="dugme dugme-belo dugme-veliko">Zatraži ponudu</a>
                    <a href="<?= url('radovi') ?>" class="dugme dugme-obrub dugme-veliko">Pogledaj radove</a>
                </div>
            </div>

            <!-- Brza procena: JavaScript salje podatke web servisu POST api/procena -->
            <div class="col-lg-5 offset-lg-1">
                <div class="kalkulator" id="kalkulator">
                    <div class="kalkulator-vrh">
                        <h2>Brza procena cene</h2>
                        <p>Okvirni iznos dobijate odmah.</p>
                    </div>

                    <div class="kalkulator-telo">
                        <label class="oznaka" for="kalkKvadratura">Kvadratura</label>
                        <div class="kalkulator-unos">
                            <input type="number" id="kalkKvadratura" class="polje" value="50" min="1" max="100000">
                            <span class="jedinica">m&sup2;</span>
                        </div>

                        <label class="oznaka mt-3">Šta vam je potrebno?</label>
                        <div class="kalkulator-usluge">
                            <?php foreach ($usluge as $i => $u): ?>
                                <label class="kvadrat-izbor">
                                    <input type="checkbox" name="kalkUsluge" value="<?= (int) $u['id'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                    <span class="kvadrat-telo">
                                        <span class="kvadrat-naziv"><?= e($u['naziv']) ?></span>
                                        <span class="kvadrat-cena"><?= e(cena($u['cena_od'])) ?>/<?= e($u['jedinica']) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="kalkulator-rezultat">
                            <div class="kalkulator-stavke" id="kalkStavke"></div>
                            <div class="kalkulator-ukupno">
                                <span>Procena</span>
                                <strong id="kalkUkupno">—</strong>
                            </div>
                            <p class="kalkulator-napomena" id="kalkNapomena">
                                Izaberite uslugu i unesite kvadraturu.
                            </p>
                        </div>

                        <button type="button" class="dugme dugme-glavno w-100" id="kalkDugme">
                            <span class="dugme-tekst">Izračunaj</span>
                            <span class="dugme-ucitavanje" hidden><span class="spinner"></span> Računam...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ BROJEVI (podaci sa api/statistika) ============ -->
<section class="brojevi" id="brojevi">
    <div class="container">
        <div class="row g-4">
            <?php
            $oznake = [
                'godina_iskustva' => 'godina iskustva',
                'radova'          => 'izvedenih radova',
                'kvadratura'      => 'obrađenih m&sup2;',
            ];
            foreach ($oznake as $kljuc => $opis):
                ?>
                <div class="col-4">
                    <div class="broj-stavka">
                        <span class="broj" data-kljuc="<?= e($kljuc) ?>"><?= number_format((float) $statistika[$kljuc], 0, ',', '.') ?></span>
                        <span class="broj-opis"><?= $opis ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ USLUGE ============ -->
<section class="odeljak">
    <div class="container">
        <div class="zaglavlje-odeljka">
            <div>
                <span class="nadnaslov">Usluge</span>
                <h2>Sve faze završnih radova</h2>
            </div>
            <p class="zaglavlje-opis">
                Radimo sopstvenom ekipom, bez podizvođača.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($usluge as $u): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require APP_ROOT . '/app/views/partials/kartica-usluge.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ O NAMA ============ -->
<section class="odeljak odeljak-sivi">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="slika-okvir">
                    <img src="<?= asset('img/o-nama.jpg') ?>" alt="Rad na gradilištu" loading="lazy">
                </div>
            </div>

            <div class="col-lg-6">
                <span class="nadnaslov">O nama</span>
                <h2>Kako radimo</h2>
                <p class="uvodni-tekst">
                    Finish Line vodi ekipa koja zajedno radi već dvanaest godina.
                    Ne uzimamo više poslova nego što možemo da završimo, pa se termini ne pomeraju.
                </p>

                <ul class="lista-prednosti">
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Ponuda koja se ne menja</strong>
                            Cena iz ponude je konačna. Dodatni radovi samo uz vašu saglasnost.
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Zaštićen prostor</strong>
                            Podovi i stolarija se pokrivaju pre početka, a prostor se čisti svaki dan.
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Provera pod reflektorom</strong>
                            Ravnost zidova kontrolišemo kosim svetlom pre primopredaje.
                        </div>
                    </li>
                </ul>

                <a href="<?= url('o-nama') ?>" class="dugme dugme-obrub-tamni">Više o nama</a>
            </div>
        </div>
    </div>
</section>

<!-- ============ RADOVI ============ -->
<section class="odeljak">
    <div class="container">
        <div class="zaglavlje-odeljka">
            <div>
                <span class="nadnaslov">Radovi</span>
                <h2>Nedavno izvedeni poslovi</h2>
            </div>
            <a href="<?= url('radovi') ?>" class="veza-strelica">Svi radovi <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($radovi as $r): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require APP_ROOT . '/app/views/partials/kartica-rada.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ KONTAKT ============ -->
<section class="odeljak odeljak-sivi" id="kontakt">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="nadnaslov">Kontakt</span>
                <h2>Recite nam šta vam treba</h2>
                <p class="uvodni-tekst">
                    Popunite formu ili nas pozovite. Za manje poslove ponudu možemo dati
                    i na osnovu fotografija.
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
                    <?php require APP_ROOT . '/app/views/partials/forma-upit.php'; ?>
                </div>
            </div>
        </div>
    </div>
</section>
