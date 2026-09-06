-- =====================================================================
--  Finish Line - zavrsni gradjevinski radovi
--  Struktura baze i demo podaci
--
--  Uvoz: izabrati svoju InfinityFree bazu u phpMyAdmin-u, pa Import -> ovaj fajl
--  Napomena za InfinityFree: prvo napraviti bazu u kontrolnom panelu,
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- ---------------------------------------------------------------------
-- Korisnici administracije
-- Lozinka se cuva iskljucivo kao hash (password_hash), nikad kao tekst.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `korisnici`;
CREATE TABLE `korisnici` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ime`               VARCHAR(100) NOT NULL,
    `email`             VARCHAR(150) NOT NULL,
    `lozinka_hash`      VARCHAR(255) NOT NULL,
    `uloga`             ENUM('admin','urednik') NOT NULL DEFAULT 'urednik',
    `aktivan`           TINYINT(1) NOT NULL DEFAULT 1,
    `poslednja_prijava` DATETIME DEFAULT NULL,
    `kreiran`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_korisnici_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Usluge
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `usluge`;
CREATE TABLE `usluge` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `naziv`       VARCHAR(120) NOT NULL,
    `slug`        VARCHAR(140) NOT NULL,
    `ikona`       VARCHAR(60)  NOT NULL DEFAULT 'bi-tools',
    `kratak_opis` VARCHAR(255) NOT NULL DEFAULT '',
    `opis`        TEXT,
    `cena_od`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `jedinica`    VARCHAR(20)  NOT NULL DEFAULT 'm2',
    `slika`       VARCHAR(255) DEFAULT NULL,
    `redosled`    INT NOT NULL DEFAULT 0,
    `aktivna`     TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_usluge_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Izvedeni radovi (portfolio / galerija)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `radovi`;
CREATE TABLE `radovi` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `naziv`         VARCHAR(160) NOT NULL,
    `usluga_id`     INT UNSIGNED DEFAULT NULL,
    `opis`          TEXT,
    `slika`         VARCHAR(255) DEFAULT NULL,
    `lokacija`      VARCHAR(120) NOT NULL DEFAULT '',
    `kvadratura`    INT UNSIGNED DEFAULT NULL,
    `trajanje_dana` INT UNSIGNED DEFAULT NULL,
    `godina`        SMALLINT UNSIGNED DEFAULT NULL,
    `izdvojen`      TINYINT(1) NOT NULL DEFAULT 0,
    `objavljen`     TINYINT(1) NOT NULL DEFAULT 1,
    `kreiran`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_radovi_usluga` (`usluga_id`),
    CONSTRAINT `fk_radovi_usluga` FOREIGN KEY (`usluga_id`)
        REFERENCES `usluge` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Dodatne fotografije rada (otpremaju se kroz web servis POST api/upload)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rad_slike`;
CREATE TABLE `rad_slike` (
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rad_id`   INT UNSIGNED NOT NULL,
    `putanja`  VARCHAR(255) NOT NULL,
    `opis`     VARCHAR(200) NOT NULL DEFAULT '',
    `redosled` INT NOT NULL DEFAULT 0,
    `kreiran`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rad_slike_rad` (`rad_id`),
    CONSTRAINT `fk_rad_slike_rad` FOREIGN KEY (`rad_id`)
        REFERENCES `radovi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela iz ranije verzije projekta, vise se ne koristi
DROP TABLE IF EXISTS `utisci`;

-- ---------------------------------------------------------------------
-- Upiti posetilaca (zahtevi za ponudu)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `upiti`;
CREATE TABLE `upiti` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ime`        VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL,
    `telefon`    VARCHAR(40)  NOT NULL DEFAULT '',
    `usluga_id`  INT UNSIGNED DEFAULT NULL,
    `kvadratura` INT UNSIGNED DEFAULT NULL,
    `poruka`     TEXT NOT NULL,
    `procena`    DECIMAL(12,2) DEFAULT NULL,
    `status`     ENUM('nov','u_obradi','zavrsen','odbijen') NOT NULL DEFAULT 'nov',
    `ip_adresa`  VARCHAR(45) NOT NULL DEFAULT '',
    `kreiran`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_upiti_status` (`status`),
    KEY `idx_upiti_usluga` (`usluga_id`),
    CONSTRAINT `fk_upiti_usluga` FOREIGN KEY (`usluga_id`)
        REFERENCES `usluge` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  DEMO PODACI
-- =====================================================================

-- Usluge ------------------------------------------------------------
INSERT INTO `usluge` (`id`, `naziv`, `slug`, `ikona`, `kratak_opis`, `opis`, `cena_od`, `jedinica`, `slika`, `redosled`, `aktivna`) VALUES
(1, 'Gletovanje', 'gletovanje', 'bi-layers',
 'Savršeno ravni zidovi i plafoni, spremni za završnu boju.',
 'Gletovanje radimo u dva do tri sloja, uz međubrušenje i kontrolu ravnosti pod reflektorom. Koristimo mašinsko nanošenje za veće površine, čime se skraćuje rok i postiže ujednačen sloj. Pre gletovanja obavezno se radi impregnacija podloge. Nakon brušenja prostor ostaje očišćen i spreman za krečenje.',
 450.00, 'm2', 'img/usluga-gletovanje.jpg', 1, 1),
(2, 'Krečenje i molerski radovi', 'krecenje', 'bi-brush',
 'Disperzivne, poludisperzivne i dekorativne boje po izboru.',
 'Krečenje izvodimo valjkom ili bezvazdušnim prskanjem, u dva sloja, uz prethodnu zaštitu podova, stolarije i nameštaja. Radimo tonirane boje po RAL i NCS karti, perive boje za kuhinje i hodnike, kao i boje bez mirisa za dečije sobe. Ivice i spojevi se obrađuju trakom, bez prelivanja.',
 250.00, 'm2', 'img/usluga-krecenje.jpg', 2, 1),
(3, 'Gips i spušteni plafoni', 'gips', 'bi-grid-3x3',
 'Spušteni plafoni, niše, rasveta i pregrade od gipsa.',
 'Izrađujemo spuštene plafone sa skrivenom LED rasvetom, gips niše, police i obloge instalacija. Konstrukcija se radi od pocinkovanih profila, sa zvučnom i termo izolacijom po potrebi. Spojevi se bandažiraju i gletuju do potpuno ravne površine. Za kupatila koristimo vlagootpornu ploču.',
 1800.00, 'm2', 'img/usluga-gips.jpg', 3, 1),
(4, 'Dekorativni malteri', 'dekorativni-malteri', 'bi-palette',
 'Venecijaner, beton izgled, mikrocement i strukturne obrade.',
 'Dekorativne obrade nanosimo ručno, u više slojeva, uz uzorak koji odobravate pre početka rada. U ponudi su venecijanski malter, imitacija betona, mikrocement za kupatila i strukturne fasadne obrade. Svaka površina je unikatna i završava se zaštitnim voskom ili lakom.',
 1500.00, 'm2', 'img/usluga-dekor.jpg', 4, 1),
(5, 'Pregradni zidovi', 'pregradni-zidovi', 'bi-bricks',
 'Brza pregradnja prostora bez mokrih procesa i lomljenja.',
 'Pregradni zidovi od gipsanih ploča na metalnoj potkonstrukciji, sa mineralnom vunom za zvučnu izolaciju. Idealno rešenje za deljenje soba, izradu garderobera i kancelarijskih pregrada. Zid je nosiv za police i televizor uz ugradnju ojačanja na dogovorenim pozicijama.',
 2200.00, 'm2', 'img/usluga-pregrade.jpg', 5, 1),
(6, 'Priprema i sanacija', 'sanacija', 'bi-tools',
 'Skidanje starih slojeva, sanacija pukotina i vlage.',
 'Pre završne obrade uklanjamo stare slojeve boje i maltera, saniramo pukotine armaturnom trakom, popravljamo oštećenja i tretiramo mesta zahvaćena vlagom i buđi. Podloga se impregnira odgovarajućim prajmerom, čime se sprečava ljuštenje i produžava vek završnog sloja.',
 350.00, 'm2', 'img/usluga-sanacija.jpg', 6, 1);

-- Radovi ------------------------------------------------------------
INSERT INTO `radovi` (`id`, `naziv`, `usluga_id`, `opis`, `slika`, `lokacija`, `kvadratura`, `trajanje_dana`, `godina`, `izdvojen`, `objavljen`) VALUES
(1, 'Stan u novogradnji, Grbavica', 1,
 'Kompletno gletovanje zidova i plafona u stanu od 78 m2. Rađeno mašinsko nanošenje u tri sloja sa međubrušenjem i kontrolom ravnosti pod kosim svetlom.',
 'img/rad-01.jpg', 'Novi Sad', 78, 6, 2026, 1, 1),
(2, 'Porodična kuća, Sremska Kamenica', 2,
 'Krečenje unutrašnjih prostorija u dve nijanse po NCS karti. Perive boje u hodniku i kuhinji, mat završnica u spavaćim sobama.',
 'img/rad-02.jpg', 'Sremska Kamenica', 145, 8, 2026, 1, 1),
(3, 'Spušteni plafon sa LED rasvetom', 3,
 'Izrada spuštenog plafona u dnevnoj sobi sa skrivenom LED trakom po obodu i ugradnjom spot rasvete.',
 'img/rad-03.jpg', 'Novi Sad', 32, 4, 2026, 1, 1),
(4, 'Kancelarijski prostor, Bulevar', 5,
 'Pregradni zidovi od gipsanih ploča sa zvučnom izolacijom, podela open space prostora na četiri kancelarije.',
 'img/rad-04.jpg', 'Novi Sad', 210, 11, 2025, 0, 1),
(5, 'Venecijaner u trpezariji', 4,
 'Dekorativni venecijanski malter u toplom tonu, nanošen u pet slojeva, završna obrada voskom sa blagim sjajem.',
 'img/rad-05.jpg', 'Beograd', 24, 5, 2025, 1, 1),
(6, 'Sanacija vlage u prizemlju', 6,
 'Uklanjanje oštećenog maltera, tretman protiv buđi, sanacioni malter i priprema podloge za završnu obradu.',
 'img/rad-06.jpg', 'Petrovaradin', 46, 7, 2025, 0, 1),
(7, 'Dvosoban stan, Liman', 1,
 'Gletovanje i krečenje kompletnog stana pred useljenje. Radovi izvedeni u kontinuitetu, bez zastoja, uz svakodnevno čišćenje.',
 'img/rad-07.jpg', 'Novi Sad', 56, 5, 2025, 0, 1),
(8, 'Mikrocement u kupatilu', 4,
 'Priprema i obrada zidova kupatila, vlagootporne ploče i završna obrada otporna na vodu.',
 'img/rad-08.jpg', 'Novi Sad', 18, 4, 2025, 0, 1),
(9, 'Lokal u centru grada', 2,
 'Krečenje i sitne popravke pred otvaranje lokala. Radovi izvedeni noću, da se ne remeti rad susednih objekata.',
 'img/rad-09.jpg', 'Novi Sad', 92, 3, 2024, 0, 1),
(10, 'Potkrovlje, Futog', 3,
 'Obloga kosina gipsanim pločama sa termo izolacijom, izrada niša i priprema za krečenje.',
 'img/rad-10.jpg', 'Futog', 64, 9, 2024, 0, 1);

-- Upiti (demo) --------------------------------------------------------
INSERT INTO `upiti` (`ime`, `email`, `telefon`, `usluga_id`, `kvadratura`, `poruka`, `procena`, `status`, `kreiran`) VALUES
('Petar Nikolić', 'petar.nikolic@example.com', '064 111 2233', 1, 65, 'Stan u novogradnji, potrebno gletovanje pre useljenja. Zanima me rok.', 29250.00, 'nov', NOW()),
('Ivana Radić', 'ivana.radic@example.com', '063 444 5566', 3, 28, 'Zanima me spušteni plafon u dnevnoj sobi sa LED rasvetom.', 50400.00, 'u_obradi', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Vladimir Simić', 'vladimir.simic@example.com', '060 777 8899', 2, 110, 'Krečenje kuće, dve nijanse. Molim ponudu.', 27500.00, 'zavrsen', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('Tamara Lukić', 'tamara.lukic@example.com', '065 222 3344', 4, 20, 'Interesuje me mikrocement u kupatilu.', 30000.00, 'nov', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Bojan Matić', 'bojan.matic@example.com', '062 888 1122', 5, 40, 'Pregrada u kancelariji, potreban izlazak na teren.', 88000.00, 'odbijen', DATE_SUB(NOW(), INTERVAL 15 DAY));

-- Korisnici -----------------------------------------------------------
INSERT INTO `korisnici` (`ime`, `email`, `lozinka_hash`, `uloga`, `aktivan`) VALUES
('Andrija Kostić', 'admin@finishline.rs', '$2y$10$qCwRi7vaQzN7NQPa6.lnNODf.8gmLqg/xZzuxTs5tSxZARiajasJy', 'admin', 1),
('Marija Urednik', 'urednik@finishline.rs', '$2y$10$npDg962BxZEGxEdgoI2/Eel8kUDhJ.1BUEFt1PGu47rsj.N9N1z7y', 'urednik', 1);

SET FOREIGN_KEY_CHECKS = 1;
