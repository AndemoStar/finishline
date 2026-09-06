<?php
$naslov        = 'O nama';
$podnaslov     = 'Ekipa za završne građevinske radove iz Velike Plane.';
$putanjaStavke = ['O nama' => null];
require APP_ROOT . '/app/views/partials/naslov-strane.php';
?>

<section class="odeljak">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="slika-okvir">
                    <img src="<?= asset('img/o-nama.jpg') ?>" alt="Rad na gradilištu">
                </div>
            </div>

            <div class="col-lg-6">
                <span class="nadnaslov">Naša priča</span>
                <h2>Počeli smo od jednog stana</h2>
                <p>
                    Finish Line je osnovan 2014. godine u Velikoj Plani. Prvi posao bio je gletovanje
                    jednosobnog stana na Limanu. Danas ekipa broji šest ljudi, a najveći deo posla
                    i dalje dolazi preko preporuke ranijih klijenata.
                </p>
                <p>
                    Radimo isključivo završne radove — gletovanje, krečenje, gips i dekorativne obrade.
                    Nismo se širili na sve i svašta, jer se kvalitet dobija ponavljanjem istog posla,
                    a ne pokrivanjem svih zanata odjednom.
                </p>
                <p>
                    Ne uzimamo više poslova nego što možemo da završimo. Zbog toga se termini
                    ne pomeraju i zbog toga možemo da damo pisanu garanciju od dve godine
                    na sve izvedene radove.
                </p>

                <div class="potpis">
                    <strong>Miloš Kostić</strong>
                    <span>osnivač i vođa ekipe</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="brojevi">
    <div class="container">
        <div class="row g-4">
            <div class="col-4">
                <div class="broj-stavka">
                    <span class="broj">12</span>
                    <span class="broj-opis">godina iskustva</span>
                </div>
            </div>
            <div class="col-4">
                <div class="broj-stavka">
                    <span class="broj"><?= (int) $brojRadova ?></span>
                    <span class="broj-opis">objavljenih radova</span>
                </div>
            </div>
            <div class="col-4">
                <div class="broj-stavka">
                    <span class="broj"><?= number_format($kvadratura, 0, ',', '.') ?></span>
                    <span class="broj-opis">obrađenih m&sup2;</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="odeljak">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="nadnaslov">Kako radimo</span>
                <h2>Četiri pravila kojih se držimo</h2>
                <p class="uvodni-tekst">
                    Ništa naročito komplikovano — samo stvari koje klijent očekuje,
                    a retko dobije.
                </p>
            </div>

            <div class="col-lg-7">
                <ul class="lista-prednosti">
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Rok je rok</strong>
                            Termin koji dogovorimo se ne pomera. Ako se pojavi problem, javljamo ga isti dan.
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Cena bez iznenađenja</strong>
                            Iznos iz ponude je konačan. Dodatni radovi se rade uz vašu saglasnost i novu stavku.
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Čisto gradilište</strong>
                            Prostor se pokriva pre početka i čisti na kraju svakog dana. Ne ostavljamo šut.
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-check-lg"></i>
                        <div>
                            <strong>Garancija u pisanoj formi</strong>
                            Dve godine garancije na izvedene radove.
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
