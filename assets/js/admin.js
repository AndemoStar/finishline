/* =====================================================================
   Administracija - Ajax radnje
     - otpremanje fotografija preko web servisa POST api/upload
     - promena statusa upita preko POST api/upiti/{id}/status
     - brisanje rada preko DELETE api/radovi/{id}
   ===================================================================== */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* --- Bocni meni na malim ekranima --- */
    const hamburger = document.getElementById('adminHamburger');
    const bocna     = document.getElementById('adminBocna');

    if (hamburger && bocna) {
        hamburger.addEventListener('click', () => bocna.classList.toggle('otvorena'));

        document.addEventListener('click', dog => {
            if (window.innerWidth < 992
                && bocna.classList.contains('otvorena')
                && !bocna.contains(dog.target)
                && !hamburger.contains(dog.target)) {
                bocna.classList.remove('otvorena');
            }
        });
    }


    /* =================================================================
       1) OTPREMANJE SLIKA PREKO WEB SERVISA
       XMLHttpRequest se koristi umesto fetch-a zato sto omogucava
       pracenje napretka otpremanja.
       ================================================================= */

    function otpremi(fajl, opcije) {
        return new Promise((resolve, reject) => {
            const podaci = new FormData();
            podaci.append('slika', fajl);
            podaci.append('grupa', opcije.grupa || 'radovi');

            if (opcije.radId) {
                podaci.append('rad_id', opcije.radId);
            }

            const zahtev = new XMLHttpRequest();
            zahtev.open('POST', FL.osnovna + 'api/upload');
            zahtev.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            zahtev.setRequestHeader('X-CSRF-Token', FL.token);

            zahtev.upload.addEventListener('progress', dog => {
                if (dog.lengthComputable && opcije.napredak) {
                    opcije.napredak(Math.round((dog.loaded / dog.total) * 100));
                }
            });

            zahtev.addEventListener('load', () => {
                let odgovor;
                try {
                    odgovor = JSON.parse(zahtev.responseText);
                } catch (e) {
                    reject(new Error('Neispravan odgovor servera.'));
                    return;
                }

                if (zahtev.status >= 200 && zahtev.status < 300 && odgovor.uspeh) {
                    resolve(odgovor.podaci);
                } else {
                    reject(new Error(odgovor.poruka || 'Otpremanje nije uspelo.'));
                }
            });

            zahtev.addEventListener('error', () => reject(new Error('Greška u vezi sa serverom.')));

            zahtev.send(podaci);
        });
    }

    /** Provera fajla pre slanja - server proverava isto jos jednom. */
    function ispravnaSlika(fajl) {
        const tipovi = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!tipovi.includes(fajl.type)) {
            FL.obavesti('Dozvoljeni formati su JPG, PNG, WEBP i GIF.', 'greska');
            return false;
        }

        if (fajl.size > 3 * 1024 * 1024) {
            FL.obavesti('Slika je veća od 3 MB.', 'greska');
            return false;
        }

        return true;
    }

    /** Povezuje zonu za prevlacenje sa poljem za izbor fajla. */
    function povezZonu(zona, polje, obradi) {
        if (!zona || !polje) return;

        zona.addEventListener('click', dog => {
            if (!dog.target.closest('.zona-ukloni')) polje.click();
        });

        polje.addEventListener('change', () => {
            if (polje.files.length) obradi(Array.from(polje.files));
            polje.value = '';
        });

        ['dragenter', 'dragover'].forEach(dog => {
            zona.addEventListener(dog, e => {
                e.preventDefault();
                zona.classList.add('preko');
            });
        });

        ['dragleave', 'drop'].forEach(dog => {
            zona.addEventListener(dog, e => {
                e.preventDefault();
                zona.classList.remove('preko');
            });
        });

        zona.addEventListener('drop', e => {
            const fajlovi = Array.from(e.dataTransfer.files);
            if (fajlovi.length) obradi(fajlovi);
        });
    }

    /* --- Naslovna slika rada --- */
    const zonaNaslovne = document.getElementById('zonaNaslovne');

    if (zonaNaslovne) {
        const polje    = document.getElementById('poljeNaslovne');
        const pregled  = document.getElementById('pregledNaslovne');
        const prazna   = document.getElementById('praznaNaslovne');
        const napredak = document.getElementById('napredakNaslovne');
        const traka    = napredak.querySelector('.traka-napretka span');
        const slikaEl  = document.getElementById('slikaNaslovne');
        const putanja  = document.getElementById('putanjaSlike');

        povezZonu(zonaNaslovne, polje, async function (fajlovi) {
            const fajl = fajlovi[0];
            if (!ispravnaSlika(fajl)) return;

            prazna.hidden    = true;
            pregled.hidden   = true;
            napredak.hidden  = false;
            traka.style.width = '0%';

            try {
                const podaci = await otpremi(fajl, {
                    grupa: zonaNaslovne.dataset.grupa,
                    napredak: p => { traka.style.width = p + '%'; }
                });

                // Strana se menja iz JavaScript-a, bez osvezavanja
                slikaEl.src      = podaci.url;
                putanja.value    = podaci.putanja;
                pregled.hidden   = false;
                napredak.hidden  = true;

                FL.obavesti('Slika je otpremljena. Ne zaboravite da sačuvate rad.');

            } catch (greska) {
                napredak.hidden = true;
                prazna.hidden   = putanja.value !== '';
                pregled.hidden  = putanja.value === '';
                FL.obavesti(greska.message, 'greska');
            }
        });

        document.getElementById('ukloniNaslovnu')?.addEventListener('click', dog => {
            dog.stopPropagation();
            putanja.value  = '';
            slikaEl.src    = '';
            pregled.hidden = true;
            prazna.hidden  = false;
        });
    }

    /* --- Dodatne fotografije rada --- */
    const zonaDodatnih = document.getElementById('zonaDodatnih');

    if (zonaDodatnih) {
        const polje    = document.getElementById('poljeDodatnih');
        const napredak = document.getElementById('napredakDodatnih');
        const traka    = napredak.querySelector('.traka-napretka span');
        const mreza    = document.getElementById('mrezaSlika');
        const brojac   = document.getElementById('brojFotografija');
        const radId    = zonaDodatnih.dataset.rad;

        povezZonu(zonaDodatnih, polje, async function (fajlovi) {
            const dobri = fajlovi.filter(ispravnaSlika);
            if (!dobri.length) return;

            napredak.hidden = false;

            for (const fajl of dobri) {
                traka.style.width = '0%';

                try {
                    const podaci = await otpremi(fajl, {
                        grupa: zonaDodatnih.dataset.grupa,
                        radId: radId,
                        napredak: p => { traka.style.width = p + '%'; }
                    });

                    const figura = document.createElement('figure');
                    figura.className = 'stavka-slike nova-kartica';
                    figura.dataset.slika = podaci.slika_id;
                    figura.innerHTML =
                        '<img src="' + podaci.url + '" alt="">' +
                        '<button type="button" class="zona-ukloni" data-obrisi-sliku="' + podaci.slika_id + '">' +
                        '<i class="bi bi-x-lg"></i></button>';

                    mreza.appendChild(figura);
                    brojac.textContent = mreza.children.length;

                } catch (greska) {
                    FL.obavesti(greska.message, 'greska');
                }
            }

            napredak.hidden = true;
            FL.obavesti('Fotografije su dodate.');
        });
    }


    /* =================================================================
       2) PROMENA STATUSA UPITA
       ================================================================= */

    document.querySelectorAll('.izbor-statusa').forEach(izbor => {
        let prethodni = izbor.value;

        izbor.addEventListener('change', async function () {
            const podaci = new FormData();
            podaci.append('status', izbor.value);

            izbor.disabled = true;

            try {
                const odgovor = await FL.servis('api/upiti/' + izbor.dataset.id + '/status', {
                    method: 'POST',
                    body: podaci
                });

                // Boja polja prati novi status
                izbor.className = 'izbor-statusa stanje-' + odgovor.podaci.status;
                prethodni = izbor.value;

                FL.obavesti('Status je sačuvan.');

            } catch (greska) {
                izbor.value = prethodni;
                FL.obavesti(greska.message, 'greska');
            } finally {
                izbor.disabled = false;
            }
        });
    });


    /* =================================================================
       3) BRISANJE RADA I FOTOGRAFIJA
       ================================================================= */

    document.addEventListener('click', async function (dog) {

        /* --- brisanje rada --- */
        const dugmeRad = dog.target.closest('[data-obrisi-rad]');

        if (dugmeRad) {
            const id    = dugmeRad.dataset.obrisiRad;
            const naziv = dugmeRad.dataset.naziv || 'ovaj rad';

            if (!confirm('Obrisati "' + naziv + '" zajedno sa svim fotografijama?')) {
                return;
            }

            try {
                await FL.servis('api/radovi/' + id, { method: 'DELETE' });

                const red = document.querySelector('tr[data-rad="' + id + '"]');
                if (red) {
                    red.style.transition = 'opacity .3s';
                    red.style.opacity = '0';
                    setTimeout(() => red.remove(), 300);
                }

                FL.obavesti('Rad je obrisan.');

            } catch (greska) {
                FL.obavesti(greska.message, 'greska');
            }
            return;
        }

        /* --- brisanje pojedinacne fotografije --- */
        const dugmeSlike = dog.target.closest('[data-obrisi-sliku]');

        if (dugmeSlike) {
            dog.stopPropagation();

            if (!confirm('Obrisati ovu fotografiju?')) return;

            const id = dugmeSlike.dataset.obrisiSliku;

            try {
                await FL.servis('api/slike/' + id, { method: 'DELETE' });
            } catch (greska) {
                FL.obavesti(greska.message, 'greska');
                return;
            }

            const figura = dugmeSlike.closest('.stavka-slike');
            const mreza  = document.getElementById('mrezaSlika');
            const brojac = document.getElementById('brojFotografija');

            if (figura) figura.remove();
            if (mreza && brojac) brojac.textContent = mreza.children.length;

            FL.obavesti('Fotografija je obrisana.');
        }
    });


    /* =================================================================
       4) PROZORI (modali)
       ================================================================= */

    /* --- puna poruka upita --- */
    const modalPoruke = document.getElementById('modalPoruke');

    if (modalPoruke) {
        const prozor = new bootstrap.Modal(modalPoruke);

        document.querySelectorAll('[data-puna-poruka]').forEach(dugme => {
            dugme.addEventListener('click', function () {
                document.getElementById('modalNaslov').textContent = 'Poruka — ' + dugme.dataset.klijent;
                document.getElementById('modalTelo').textContent   = dugme.dataset.punaPoruka;
                prozor.show();
            });
        });
    }

    /* --- izmena usluge --- */
    const modalUsluge = document.getElementById('modalUsluge');

    if (modalUsluge) {
        const prozor = new bootstrap.Modal(modalUsluge);

        document.querySelectorAll('[data-uredi-uslugu]').forEach(dugme => {
            dugme.addEventListener('click', function () {
                const u = JSON.parse(dugme.dataset.urediUslugu);

                document.getElementById('uslugaId').value       = u.id;
                document.getElementById('uslugaNaziv').value    = u.naziv;
                document.getElementById('uslugaIkona').value    = u.ikona;
                document.getElementById('uslugaKratak').value   = u.kratak_opis;
                document.getElementById('uslugaOpis').value     = u.opis || '';
                document.getElementById('uslugaCena').value     = u.cena_od;
                document.getElementById('uslugaJedinica').value = u.jedinica;
                document.getElementById('uslugaRedosled').value = u.redosled;
                document.getElementById('uslugaAktivna').checked = u.aktivna === 1;

                prozor.show();
            });
        });
    }
});
