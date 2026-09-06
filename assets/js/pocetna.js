/* =====================================================================
   Pocetna strana:
     1) brojevi se dovlace sa web servisa GET api/statistika i animiraju
     2) kalkulator salje podatke servisu POST api/procena
   Oba primera menjaju sadrzaj strane iz JavaScript-a, bez osvezavanja.
   ===================================================================== */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* =============== 1) BROJEVI SA WEB SERVISA =============== */

    const odeljakBrojeva = document.getElementById('brojevi');

    async function ucitajStatistiku() {
        try {
            const odgovor = await FL.servis('api/statistika');
            if (!odgovor.uspeh) return;

            document.querySelectorAll('.broj').forEach(el => {
                const kljuc = el.dataset.kljuc;
                if (odgovor.podaci[kljuc] !== undefined) {
                    animirajBroj(el, Number(odgovor.podaci[kljuc]));
                }
            });
        } catch (e) {
            // Ako servis nije dostupan, strana i dalje radi - samo bez animacije
            console.warn('Statistika nije ucitana:', e.message);
        }
    }

    function animirajBroj(element, ciljna) {
        const trajanje = 1400;
        const pocetak  = performance.now();

        function korak(sada) {
            const napredak = Math.min((sada - pocetak) / trajanje, 1);
            // usporavanje pred kraj
            const olaksano = 1 - Math.pow(1 - napredak, 3);
            element.textContent = FL.broj(Math.floor(ciljna * olaksano));

            if (napredak < 1) {
                requestAnimationFrame(korak);
            } else {
                element.textContent = FL.broj(ciljna);
            }
        }

        requestAnimationFrame(korak);
    }

    // Brojevi se osvezavaju kada odeljak dodje u vidno polje.
    // Tacne vrednosti su vec ispisane sa servera, pa strana radi i
    // bez JavaScript-a - Ajax ih samo animira i osvezava.
    if (odeljakBrojeva) {
        let pokrenuto = false;

        const pokreni = () => {
            if (pokrenuto) return;
            pokrenuto = true;
            ucitajStatistiku();
        };

        if ('IntersectionObserver' in window) {
            const posmatrac = new IntersectionObserver((stavke, posm) => {
                if (stavke[0].isIntersecting) {
                    posm.disconnect();
                    pokreni();
                }
            }, { threshold: .3 });

            posmatrac.observe(odeljakBrojeva);

            // Sigurnosna mreza: ako posmatrac iz bilo kog razloga ne
            // reaguje (skrivena kartica, nulta visina prozora), brojevi
            // se osvezavaju posle tri sekunde.
            setTimeout(pokreni, 3000);
        } else {
            pokreni();
        }
    }


    /* =============== 2) KALKULATOR PROCENE =============== */

    const kalkulator = document.getElementById('kalkulator');
    if (!kalkulator) return;

    const dugme      = document.getElementById('kalkDugme');
    const poljeKvadr = document.getElementById('kalkKvadratura');
    const stavkeEl   = document.getElementById('kalkStavke');
    const ukupnoEl   = document.getElementById('kalkUkupno');
    const napomenaEl = document.getElementById('kalkNapomena');

    async function izracunaj() {
        const kvadratura = parseInt(poljeKvadr.value, 10);
        const izabrane   = Array.from(
            kalkulator.querySelectorAll('input[name="kalkUsluge"]:checked')
        ).map(el => el.value);

        // Provera na klijentu (server proverava isto, nezavisno od ovoga)
        if (!kvadratura || kvadratura < 1) {
            prikaziGresku('Unesite kvadraturu prostora.');
            poljeKvadr.focus();
            return;
        }

        if (izabrane.length === 0) {
            prikaziGresku('Izaberite bar jednu uslugu.');
            return;
        }

        const podaci = new FormData();
        podaci.append('kvadratura', String(kvadratura));
        izabrane.forEach(id => podaci.append('usluge[]', id));

        FL.ucitavanje(dugme, true);

        try {
            const odgovor = await FL.servis('api/procena', { method: 'POST', body: podaci });
            prikaziRezultat(odgovor.podaci);
        } catch (greska) {
            prikaziGresku(greska.message);
        } finally {
            FL.ucitavanje(dugme, false);
        }
    }

    /** Ispisuje rezultat - sadrzaj strane se menja iz JavaScript-a. */
    function prikaziRezultat(podaci) {
        stavkeEl.innerHTML = podaci.stavke.map(s =>
            '<div class="stavka"><span>' + FL.bezbedno(s.naziv) + '</span>'
          + '<span>' + FL.bezbedno(s.prikaz) + '</span></div>'
        ).join('');

        ukupnoEl.textContent = podaci.ukupno_prikaz;
        napomenaEl.textContent = podaci.napomena;
        napomenaEl.classList.remove('greska-tekst');

        // kratka animacija iznosa
        ukupnoEl.animate(
            [{ transform: 'scale(1)' }, { transform: 'scale(1.08)' }, { transform: 'scale(1)' }],
            { duration: 380, easing: 'ease-out' }
        );
    }

    function prikaziGresku(tekst) {
        stavkeEl.innerHTML = '';
        ukupnoEl.textContent = '—';
        napomenaEl.textContent = tekst;
        napomenaEl.classList.add('greska-tekst');
    }

    dugme.addEventListener('click', izracunaj);

    // Enter u polju za kvadraturu takodje racuna
    poljeKvadr.addEventListener('keydown', dog => {
        if (dog.key === 'Enter') {
            dog.preventDefault();
            izracunaj();
        }
    });
});
