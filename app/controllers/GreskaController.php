<?php
declare(strict_types=1);

/**
 * Prikaz stranica sa greskama.
 */
final class GreskaController extends Controller
{
    public function nepostojeca(): void
    {
        http_response_code(404);

        $this->prikaz('greske/404', [
            'naslovStrane' => 'Strana nije pronađena - ' . APP_NAME,
        ]);
    }

    public function zabranjeno(): void
    {
        http_response_code(403);

        $this->prikaz('greske/403', [
            'naslovStrane' => 'Pristup nije dozvoljen - ' . APP_NAME,
        ]);
    }

    public function greskaServera(): void
    {
        http_response_code(500);

        $this->prikaz('greske/500', [
            'naslovStrane' => 'Greška na serveru - ' . APP_NAME,
        ]);
    }
}
