/* =====================================================================
   Galerija radova.
   Filtriranje po usluzi i dugme "Učitaj još" rade preko web servisa
   GET api/radovi, bez ponovnog ucitavanja strane. Kartice se prave
   u JavaScript-u i dodaju u stranu.
   ===================================================================== */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const spisak = document.getElementById('spisakRadova');
    if (!spisak) return;

    const filteri   = document.querySelectorAll('.filter');
    const dugmeJos  = document.getElementById('dugmeJos');
    const stanje    = document.getElementById('stanjeGalerije');
    const brojacEl  = document.getElementById('brojRezultata');

    const poStrani  = parseInt(spisak.dataset.poStrani || '9', 10);

    let usluga = spisak.dataset.usluga || '';
    let pomak  = parseInt(spisak.dataset.ucitano || '0', 10);
    let ukupno = parseInt(spisak.dataset.ukupno || '0', 10);

    /** Pravi HTML jedne kartice od podataka koje je vratio servis. */
    function napraviKarticu(r) {
        const kolona = document.createElement('div');
        kolona.className = 'col-md-6 col-lg-4 nova-kartica';

        // Isti raspored kao u partials/kartica-rada.php
        const meta = [];
        if (r.usluga)     meta.push('<li>' + FL.bezbedno(r.usluga) + '</li>');
        if (r.lokacija)   meta.push('<li>' + FL.bezbedno(r.lokacija) + '</li>');
        if (r.kvadratura) meta.push('<li>' + r.kvadratura + ' m&sup2;</li>');

        kolona.innerHTML =
            '<article class="kartica-rad">' +
                '<a class="kartica-rad-slika" href="' + r.url + '">' +
                    '<img src="' + r.slika + '" alt="' + FL.bezbedno(r.naziv) + '" loading="lazy">' +
                '</a>' +
                '<div class="kartica-rad-telo">' +
                    '<h3><a href="' + r.url + '">' + FL.bezbedno(r.naziv) + '</a></h3>' +
                    (meta.length ? '<ul class="kartica-rad-meta">' + meta.join('') + '</ul>' : '') +
                '</div>' +
            '</article>';

        return kolona;
    }

    /**
     * Dovlaci radove sa servisa.
     * @param {boolean} ispocetka true kada se menja filter
     */
    async function ucitaj(ispocetka) {
        const parametri = new URLSearchParams({
            limit: String(poStrani),
            pomak: String(ispocetka ? 0 : pomak)
        });

        if (usluga) parametri.append('usluga', usluga);

        if (dugmeJos) FL.ucitavanje(dugmeJos, true);
        if (ispocetka) spisak.classList.add('ucitava');

        try {
            const odgovor = await FL.servis('api/radovi?' + parametri.toString());

            if (ispocetka) {
                spisak.innerHTML = '';
                pomak = 0;
            }

            odgovor.podaci.forEach(r => spisak.appendChild(napraviKarticu(r)));

            pomak += odgovor.podaci.length;
            ukupno = odgovor.ukupno;

            osveziStanje(odgovor.ima_jos);

        } catch (greska) {
            FL.obavesti(greska.message, 'greska');
        } finally {
            if (dugmeJos) FL.ucitavanje(dugmeJos, false);
            spisak.classList.remove('ucitava');
        }
    }

    /** Osvezava brojac rezultata i vidljivost dugmeta. */
    function osveziStanje(imaJos) {
        if (brojacEl) {
            brojacEl.textContent = ukupno === 0
                ? 'Nema radova za izabranu uslugu'
                : 'Prikazano ' + pomak + ' od ' + ukupno;
        }

        if (dugmeJos) {
            dugmeJos.hidden = !imaJos;
        }

        if (stanje) {
            stanje.hidden = ukupno !== 0;
        }
    }

    /* --- Klik na filter --- */
    filteri.forEach(dugme => {
        dugme.addEventListener('click', function () {
            filteri.forEach(d => d.classList.remove('aktivan'));
            dugme.classList.add('aktivan');

            usluga = dugme.dataset.usluga || '';

            // Adresa u pregledacu prati izabrani filter, da moze da se podeli
            const adresa = new URL(window.location.href);
            if (usluga) {
                adresa.searchParams.set('usluga', usluga);
            } else {
                adresa.searchParams.delete('usluga');
            }
            history.replaceState(null, '', adresa.toString());

            ucitaj(true);
        });
    });

    /* --- Ucitaj jos --- */
    if (dugmeJos) {
        dugmeJos.addEventListener('click', () => ucitaj(false));
    }

    // Pocetno stanje dugmeta (prva strana je vec iscrtana na serveru)
    osveziStanje(pomak < ukupno);
});
