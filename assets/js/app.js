/* =====================================================================
   Finish Line - zajednicki JavaScript
   Sadrzi pomocne funkcije za rad sa web servisima i ponasanje
   koje je isto na svim stranama.
   ===================================================================== */

'use strict';

const FL = (function () {

    const osnovna = document.querySelector('meta[name="osnovna-adresa"]')?.content || '/';
    const token   = document.querySelector('meta[name="csrf-token"]')?.content || '';

    /**
     * Poziv web servisa.
     * Uz svaki zahtev se salje CSRF token i zaglavlje po kome server
     * prepoznaje da je rec o Ajax pozivu.
     */
    async function servis(putanja, opcije = {}) {
        const podesavanja = {
            method: opcije.method || 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-Token': token,
                ...(opcije.headers || {})
            }
        };

        if (opcije.body) {
            podesavanja.body = opcije.body;
        }

        const odgovor = await fetch(osnovna + putanja, podesavanja);

        let podaci;
        try {
            podaci = await odgovor.json();
        } catch (e) {
            throw new Error('Server je vratio odgovor koji nije u JSON formatu.');
        }

        if (!odgovor.ok) {
            const greska = new Error(podaci.poruka || 'Zahtev nije uspeo.');
            greska.podaci = podaci;
            greska.status = odgovor.status;
            throw greska;
        }

        return podaci;
    }

    /** Priprema tekst pre ubacivanja u stranu (zastita od XSS). */
    function bezbedno(tekst) {
        const d = document.createElement('div');
        d.textContent = tekst === null || tekst === undefined ? '' : String(tekst);
        return d.innerHTML;
    }

    /** Format broja: 12500 -> "12.500" */
    function broj(vrednost) {
        return new Intl.NumberFormat('sr-RS', { maximumFractionDigits: 0 }).format(vrednost);
    }

    /** Format cene: 12500 -> "12.500 RSD" */
    function dinara(vrednost) {
        return broj(vrednost) + ' RSD';
    }

    /** Kratko obavestenje na dnu ekrana. */
    function obavesti(tekst, tip = 'uspeh') {
        const stara = document.querySelector('.obavestenje');
        if (stara) stara.remove();

        const el = document.createElement('div');
        el.className = 'obavestenje obavestenje-' + tip;
        el.innerHTML = '<i class="bi ' + (tip === 'uspeh' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') + '"></i>'
                     + '<span>' + bezbedno(tekst) + '</span>';
        document.body.appendChild(el);

        requestAnimationFrame(() => el.classList.add('vidljivo'));
        setTimeout(() => {
            el.classList.remove('vidljivo');
            setTimeout(() => el.remove(), 300);
        }, 3600);
    }

    /** Ukljucuje/iskljucuje stanje ucitavanja na dugmetu. */
    function ucitavanje(dugme, aktivno) {
        if (!dugme) return;
        const tekst = dugme.querySelector('.dugme-tekst');
        const spin  = dugme.querySelector('.dugme-ucitavanje');

        dugme.disabled = aktivno;
        if (tekst) tekst.hidden = aktivno;
        if (spin)  spin.hidden  = !aktivno;
    }

    return { servis, bezbedno, broj, dinara, obavesti, ucitavanje, osnovna, token };
})();


/* --------------------------------------------------------------------
   Ponasanje zajednicko za sve strane
   -------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {

    /* --- Senka na zaglavlju pri skrolovanju --- */
    const zaglavlje = document.getElementById('zaglavlje');
    const naVrh     = document.getElementById('naVrh');

    function priSkrolu() {
        const y = window.scrollY;
        if (zaglavlje) zaglavlje.classList.toggle('zalepljeno', y > 20);
        if (naVrh)     naVrh.classList.toggle('vidljivo', y > 500);
    }

    window.addEventListener('scroll', priSkrolu, { passive: true });
    priSkrolu();

    if (naVrh) {
        naVrh.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    /* --- Zatvaranje poruka --- */
    document.querySelectorAll('.poruka-zatvori').forEach(dugme => {
        dugme.addEventListener('click', () => dugme.closest('.poruka').remove());
    });

    /* --- Pojavljivanje elemenata pri skrolovanju --- */
    const zaPojavu = document.querySelectorAll('.odeljak .kartica-usluga, .odeljak .kartica-rad, .korak, .kartica-utisak');

    if ('IntersectionObserver' in window && zaPojavu.length) {
        zaPojavu.forEach(el => el.classList.add('pojava'));

        const posmatrac = new IntersectionObserver((stavke, posm) => {
            stavke.forEach((stavka, i) => {
                if (stavka.isIntersecting) {
                    setTimeout(() => stavka.target.classList.add('vidljiva'), i * 70);
                    posm.unobserve(stavka.target);
                }
            });
        }, { threshold: .12, rootMargin: '0px 0px -60px 0px' });

        zaPojavu.forEach(el => posmatrac.observe(el));
    }

    /* --- Uvecavanje slika (lightbox) --- */
    const boks   = document.getElementById('svetloBoks');
    const slikaEl= document.getElementById('svetloSlika');
    const opisEl = document.getElementById('svetloOpis');

    let galerija = [];
    let trenutna = 0;

    function otvori(indeks) {
        if (!boks || !galerija.length) return;
        trenutna = (indeks + galerija.length) % galerija.length;
        slikaEl.src = galerija[trenutna].src;
        slikaEl.alt = galerija[trenutna].opis;
        opisEl.textContent = galerija[trenutna].opis;
        boks.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function zatvori() {
        if (!boks) return;
        boks.hidden = true;
        slikaEl.src = '';
        document.body.style.overflow = '';
    }

    // Klik na bilo koju sliku obelezenu sa data-uvecaj
    document.addEventListener('click', function (dog) {
        const cilj = dog.target.closest('[data-uvecaj]');
        if (!cilj) return;

        dog.preventDefault();

        const spisak = cilj.closest('[data-galerija]') || document;
        const sve    = Array.from(spisak.querySelectorAll('[data-uvecaj]'));

        galerija = sve.map(el => ({
            src:  el.dataset.uvecaj || el.getAttribute('href') || el.querySelector('img')?.src,
            opis: el.dataset.opis || el.querySelector('img')?.alt || ''
        }));

        otvori(sve.indexOf(cilj));
    });

    if (boks) {
        boks.querySelector('.svetlo-zatvori').addEventListener('click', zatvori);
        boks.querySelector('.levo').addEventListener('click', () => otvori(trenutna - 1));
        boks.querySelector('.desno').addEventListener('click', () => otvori(trenutna + 1));
        boks.addEventListener('click', dog => { if (dog.target === boks) zatvori(); });

        document.addEventListener('keydown', function (dog) {
            if (boks.hidden) return;
            if (dog.key === 'Escape')     zatvori();
            if (dog.key === 'ArrowLeft')  otvori(trenutna - 1);
            if (dog.key === 'ArrowRight') otvori(trenutna + 1);
        });
    }

    /* --- Brojac znakova u textarea poljima --- */
    const poruka = document.getElementById('poruka');
    const brojac = document.getElementById('brojacPoruke');

    if (poruka && brojac) {
        const osvezi = () => { brojac.textContent = poruka.value.length; };
        poruka.addEventListener('input', osvezi);
        osvezi();
    }

    /* --- Zatvaranje mobilnog menija posle klika na vezu --- */
    document.querySelectorAll('#glavniMeni .nav-link').forEach(veza => {
        veza.addEventListener('click', () => {
            const meni = document.getElementById('glavniMeni');
            if (meni && meni.classList.contains('show')) {
                bootstrap.Collapse.getInstance(meni)?.hide();
            }
        });
    });
});
