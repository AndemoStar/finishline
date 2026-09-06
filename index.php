<?php
/**
 * Finish Line - zavrsni gradjevinski radovi
 * Ispitni projekat: PHP + MySQL, MVC arhitektura
 *
 * Ovo je jedina ulazna tacka aplikacije (front controller).
 * Sve adrese .htaccess preusmerava ovde, a odavde ih preuzima ruter.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Aplikacija.php';

$aplikacija = new Aplikacija();
$aplikacija->pokreni();
