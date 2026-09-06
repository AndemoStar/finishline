/* =====================================================================
   Slanje upita bez osvezavanja strane.
   Forma se salje web servisu POST api/upiti, a odgovor servisa
   se ispisuje na strani.
   ===================================================================== */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const forma = document.getElementById('formaUpit');
    if (!forma) return;

    const dugme   = document.getElementById('dugmeSlanje');
    const odgovor = document.getElementById('formaOdgovor');

    /** Brise sve poruke o greskama iz prethodnog slanja. */
    function ocistiGreske() {
        forma.querySelectorAll('.greska-polja').forEach(el => { el.textContent = ''; });
        forma.querySelectorAll('.polje-greska').forEach(el => el.classList.remove('polje-greska'));
        odgovor.hidden = true;
        odgovor.className = 'forma-odgovor';
    }

    /** Ispisuje greske po poljima onako kako ih je vratio server. */
    function prikaziGreske(greske) {
        Object.keys(greske || {}).forEach(polje => {
            const poruka = forma.querySelector('[data-greska="' + polje + '"]');
            const unos   = forma.querySelector('[name="' + polje + '"]');

            if (poruka) poruka.textContent = greske[polje];
            if (unos)   unos.classList.add('polje-greska');
        });

        const prvo = forma.querySelector('.polje-greska');
        if (prvo) {
            prvo.focus();
            prvo.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function prikaziOdgovor(tekst, tip) {
        odgovor.className = 'forma-odgovor ' + tip;
        odgovor.innerHTML = '<i class="bi ' + (tip === 'uspeh' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + '"></i>'
                          + '<span>' + FL.bezbedno(tekst) + '</span>';
        odgovor.hidden = false;
        odgovor.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    forma.addEventListener('submit', async function (dog) {
        dog.preventDefault();
        ocistiGreske();

        const podaci = new FormData(forma);

        // Ako je posetilac koristio kalkulator, salje se i procenjeni iznos
        const ukupno = document.getElementById('kalkUkupno');
        if (ukupno && ukupno.textContent !== '—') {
            const broj = ukupno.textContent.replace(/[^0-9]/g, '');
            if (broj) podaci.append('procena', broj);
        }

        FL.ucitavanje(dugme, true);

        try {
            const rezultat = await FL.servis('api/upiti', { method: 'POST', body: podaci });

            prikaziOdgovor(rezultat.poruka, 'uspeh');
            forma.reset();

            const brojac = document.getElementById('brojacPoruke');
            if (brojac) brojac.textContent = '0';

        } catch (greska) {
            if (greska.podaci && greska.podaci.greske) {
                prikaziGreske(greska.podaci.greske);
            }
            prikaziOdgovor(greska.message, 'greska');

        } finally {
            FL.ucitavanje(dugme, false);
        }
    });

    /* Provera pojedinacnog polja cim korisnik izadje iz njega */
    forma.querySelectorAll('input[required], textarea[required]').forEach(polje => {
        polje.addEventListener('blur', function () {
            const poruka = forma.querySelector('[data-greska="' + polje.name + '"]');
            if (!poruka) return;

            if (polje.value.trim() === '') {
                poruka.textContent = 'Ovo polje je obavezno.';
                polje.classList.add('polje-greska');
            } else if (polje.type === 'email' && !polje.value.includes('@')) {
                poruka.textContent = 'Unesite ispravnu email adresu.';
                polje.classList.add('polje-greska');
            } else {
                poruka.textContent = '';
                polje.classList.remove('polje-greska');
            }
        });
    });
});
