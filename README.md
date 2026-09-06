# Finish Line — završni građevinski radovi

Veb aplikacija za firmu koja izvodi završne građevinske radove: gletovanje, krečenje,
gips i spuštene plafone, dekorativne maltere, pregradne zidove i sanaciju podloge.

Ispitni projekat — **PHP + MySQL, MVC arhitektura**, bez upotrebe gotovog radnog okvira.

---

## Šta aplikacija radi

Aplikacija ima **javni** i **interni** deo.

### Javni deo — da firma dođe do posla

- **Početna strana** sa kalkulatorom koji na osnovu kvadrature i izabranih usluga
  odmah daje okvirnu cenu (poziv web servisa, bez osvežavanja strane).
- **Usluge i cenovnik** — podaci se čitaju iz baze, svaka usluga ima svoju stranu.
- **Galerija izvedenih radova** sa filtriranjem po usluzi i dugmetom „Učitaj još“ —
  oboje rade preko web servisa, strana se ne osvežava.
- **Utisci klijenata** — prikazuju se samo oni koje administrator odobri.
- **Forma za upit** koja se šalje Ajax-om, sa proverom podataka na serveru
  i ispisom grešaka po poljima.

### Interni deo — administracija

- Pregled **upita** sa promenom statusa u toku rada (nov → u obradi → završen / odbijen),
  bez osvežavanja strane.
- Unos i izmena **radova** u galeriji, sa **otpremanjem fotografija kroz web servis**
  (prevlačenjem slike ili izborom fajla, uz prikaz napretka otpremanja).
- Izmena **usluga i cena**.
- Odobravanje **utisaka** pre nego što se pojave na sajtu.

---

## Tehnologija

| Sloj | Tehnologija |
|---|---|
| Server | PHP 8 (bez radnog okvira), PDO |
| Baza | MySQL / MariaDB |
| Prikaz | HTML5, CSS3, Bootstrap 5, Bootstrap Icons |
| Klijent | JavaScript (bez biblioteka), Fetch API i XMLHttpRequest |
| Servisi | Sopstveni REST servisi koji vraćaju JSON |

---

## MVC arhitektura

Sve adrese prolaze kroz jednu ulaznu tačku (`index.php`), odakle ih ruter prosleđuje
odgovarajućem kontroleru.

```
index.php                 ulazna tačka (front controller)
.htaccess                 preusmeravanje svih adresa na index.php

config/
  config.php              podaci o firmi, baza, putanje
  config.local.php        lozinka baze za hosting (NIJE u repozitorijumu)

app/
  core/
    Aplikacija.php        učitavanje klasa, spisak svih ruta, pokretanje
    Router.php            preslikavanje adrese na kontroler i metodu
    Controller.php        osnova svih kontrolera (prikaz, JSON, CSRF)
    Model.php             osnova svih modela (PDO, pripremljeni upiti)
    Database.php          jedna PDO veza za ceo zahtev
    Auth.php              prijava, uloge, kontrola pristupa
    Csrf.php              zaštita formi i Ajax poziva
    Upload.php            prijem i provera otpremljenih slika
    pomocne.php           pomoćne funkcije za prikaze

  models/                 JEDINO mesto na kom se piše SQL
    Korisnik.php  Usluga.php  Rad.php  Utisak.php  Upit.php

  controllers/            primaju zahtev, zovu model, biraju prikaz
    HomeController.php    UslugeController.php   RadoviController.php
    StraneController.php  KontaktController.php  AuthController.php
    AdminController.php   ApiController.php      GreskaController.php

  views/                  samo prikaz, bez poslovne logike
    layouts/   main.php (sajt), admin.php (administracija), prazan.php (prijava)
    partials/  zaglavlje, podnožje, kartice, forma upita
    home/ usluge/ radovi/ strane/ kontakt/ auth/ admin/ greske/

assets/
  css/  style.css, admin.css
  js/   app.js, pocetna.js, galerija.js, upit.js, admin.js
  img/  fotografije (JPG) i favicon
  uploads/  fotografije otpremljene kroz administraciju

sql/finishline.sql        struktura baze i demo podaci
install.php               pravi bazu za lokalno testiranje
```

**Podela odgovornosti:** u kontrolerima nema SQL-a, u modelima nema HTML-a,
u prikazima nema poslovne logike.

---

## Baza podataka

| Tabela | Sadržaj |
|---|---|
| `korisnici` | nalozi za administraciju (lozinka isključivo kao hash) |
| `usluge` | usluge sa cenom po jedinici mere |
| `radovi` | izvedeni radovi, povezani sa uslugom (strani ključ) |
| `rad_slike` | dodatne fotografije rada, otpremljene kroz web servis |
| `utisci` | utisci klijenata, uz polje za odobrenje |
| `upiti` | upiti poslati sa sajta, sa statusom obrade |

---

## Web servisi (REST)

Svi servisi vraćaju JSON u obliku `{ "uspeh": true|false, "podaci": ..., "poruka": "..." }`.

| Metoda | Adresa | Opis | Pristup |
|---|---|---|---|
| GET | `api/usluge` | spisak usluga | javno |
| GET | `api/radovi?usluga=3&limit=9&pomak=0` | radovi, filtriranje i stranannje | javno |
| GET | `api/radovi/{id}` | jedan rad sa fotografijama | javno |
| GET | `api/utisci?limit=6` | odobreni utisci | javno |
| GET | `api/statistika` | brojevi za početnu stranu | javno |
| POST | `api/procena` | procena cene po kvadraturi | javno |
| POST | `api/upiti` | slanje upita sa sajta | javno + CSRF |
| GET | `api/upiti` | spisak upita | samo admin |
| POST | `api/upiti/{id}/status` | promena statusa upita | samo admin |
| POST | `api/upload` | otpremanje fotografije | samo admin |
| DELETE | `api/radovi/{id}` | brisanje rada sa fotografijama | samo admin |
| DELETE | `api/slike/{id}` | brisanje jedne fotografije | samo admin |

Primer poziva iz komandne linije:

```bash
curl "http://localhost/finishline/api/radovi?usluga=3&limit=3"
```

---

## Ajax i izmena strane iz JavaScript-a

| Gde | Šta se dešava |
|---|---|
| Početna — kalkulator | šalje kvadraturu i usluge na `api/procena`, ispisuje stavke i ukupan iznos |
| Početna — brojevi | dovlači `api/statistika` i animira brojeve od nule |
| Galerija radova | filtriranje i „Učitaj još“ prave nove kartice u JavaScript-u i menjaju adresu u pregledaču |
| Forma za upit | šalje se na `api/upiti`, greške se ispisuju ispod pojedinačnih polja |
| Administracija — upiti | promena statusa se odmah čuva, boja polja prati novi status |
| Administracija — radovi | otpremanje slike sa prikazom napretka, brisanje reda iz tabele bez osvežavanja |
| Galerija slika | klik otvara uvećan prikaz sa strelicama i tastaturom |

---

## Bezbednost

- **Lozinke** se čuvaju isključivo kao hash (`password_hash`, `PASSWORD_DEFAULT`),
  provera ide preko `password_verify`. U bazi nema čitljivih lozinki.
  Ako se promeni podrazumevani algoritam, lozinka se prilikom prijave prehešuje.
- **SQL injection** nije moguć: svi upiti idu kroz PDO pripremljene upite sa parametrima,
  uz isključenu emulaciju (`ATTR_EMULATE_PREPARES = false`). Vrednosti koje se ne mogu
  vezati kao parametar (`LIMIT`, `OFFSET`) prethodno se pretvaraju u ceo broj i ograničavaju.
- **XSS**: sve što se ispisuje u HTML prolazi kroz `htmlspecialchars` (funkcija `e()`),
  a sadržaj koji ubacuje JavaScript kroz funkciju `FL.bezbedno()`.
- **CSRF**: svaka forma i svaki Ajax poziv koji menja podatke nosi token iz sesije;
  poređenje ide preko `hash_equals`. Zahtev bez ispravnog tokena se odbija.
- **Neovlašćeni pristup** se proverava na serveru pre svake radnje
  (`Auth::zahtevajAdmina()`), a ne skrivanjem linkova u prikazu.
  Web servisi vraćaju 401 ili 403 u JSON obliku.
- **Otpremanje fajlova**: proverava se stvarni sadržaj slike (`getimagesize`),
  a ne ime fajla; dozvoljeni su samo JPG, PNG, WEBP i GIF do 3 MB; fajl dobija
  novo nasumično ime; u `assets/uploads/` je `.htaccess`-om onemogućeno izvršavanje skripti.
- **Sesija**: kolačić je `HttpOnly` i `SameSite=Lax`, posle prijave se izdaje nov ID sesije
  (zaštita od session fixation). Posle pet pogrešnih pokušaja prijava se privremeno zaključava.
- Folderi `app/` i `config/` nisu dostupni iz pregledača.
- Poruka „Pogrešan email ili lozinka“ je ista i za nepostojeći nalog i za pogrešnu lozinku,
  da se ne otkriva koji nalozi postoje.

---

## Postavljanje na XAMPP

1. Kopirati folder `finishline` u `C:\xampp\htdocs\`.
2. U XAMPP kontrolnoj tabli pokrenuti **Apache** i **MySQL**.
3. Otvoriti <http://localhost/finishline/install.php> i kliknuti **Napravi bazu**.
   Skripta pravi bazu `finishline`, sve tabele i demo podatke.
   *(Alternativa: u phpMyAdmin uvesti `sql/finishline.sql`.)*
4. Otvoriti <http://localhost/finishline/>.
5. Obrisati `install.php` kada instalacija prođe.

Ako aplikacija stoji u podfolderu (npr. `htdocs/sup25/ak/`), ništa se ne podešava —
osnovna adresa se računa sama.

### Postavljanje na hosting

1. Prekopirati sve fajlove osim `install.php`.
2. Bazu uvesti kroz phpMyAdmin iz `sql/finishline.sql`
   (obrisati red `CREATE DATABASE` i `USE` ako hosting sam pravi bazu).
3. Napraviti `config/config.local.php` po uzoru na `config/config.local.example.php`
   i upisati podatke baze, uz `define('DEBUG', false);`.

---

## Nalozi za prijavu

Prijava se nalazi na <http://localhost/finishline/prijava>.

| Uloga | Email | Lozinka | Pristup |
|---|---|---|---|
| Administrator | `admin@finishline.rs` | `Demo1234` | pun pristup administraciji |
| Urednik | `urednik@finishline.rs` | `Demo1234` | **nema** pristup — služi za proveru kontrole prava |

Svi podaci u demo bazi su izmišljeni.

---

## Napomena o fotografijama

Fotografije u `assets/img/` su preuzete sa servisa [Pexels](https://www.pexels.com),
pod Pexels licencom koja dozvoljava besplatnu upotrebu bez navođenja autora.
Služe kao privremena zamena dok se ne postave prave fotografije firme —
one se dodaju kroz administraciju, otpremanjem slike za svaki rad.
