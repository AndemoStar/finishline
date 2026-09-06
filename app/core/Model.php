<?php
declare(strict_types=1);

/**
 * Zajednicka osnova svih modela.
 *
 * Modeli su jedini sloj u kome se pise SQL. Kontroleri zovu metode
 * modela i nikada ne sastavljaju upite sami.
 */
abstract class Model
{
    protected PDO $db;
    protected string $tabela = '';
    protected string $kljuc  = 'id';

    public function __construct()
    {
        $this->db = Database::veza();
    }

    /** Izvrsava upit sa parametrima i vraca PDOStatement. */
    protected function upit(string $sql, array $parametri = []): PDOStatement
    {
        $st = $this->db->prepare($sql);
        $st->execute($parametri);
        return $st;
    }

    /** Vraca sve redove upita. */
    protected function sviRedovi(string $sql, array $parametri = []): array
    {
        return $this->upit($sql, $parametri)->fetchAll();
    }

    /** Vraca prvi red upita ili null. */
    protected function jedanRed(string $sql, array $parametri = []): ?array
    {
        $red = $this->upit($sql, $parametri)->fetch();
        return $red === false ? null : $red;
    }

    /** Vraca jednu vrednost iz prvog reda. */
    protected function jednaVrednost(string $sql, array $parametri = [])
    {
        return $this->upit($sql, $parametri)->fetchColumn();
    }

    /** Zapis po primarnom kljucu. */
    public function nadji(int $id): ?array
    {
        return $this->jedanRed("SELECT * FROM {$this->tabela} WHERE {$this->kljuc} = ?", [$id]);
    }

    /** Broj zapisa u tabeli. */
    public function prebroj(): int
    {
        return (int) $this->jednaVrednost("SELECT COUNT(*) FROM {$this->tabela}");
    }

    /** Brisanje po primarnom kljucu. */
    public function obrisi(int $id): bool
    {
        return $this->upit("DELETE FROM {$this->tabela} WHERE {$this->kljuc} = ?", [$id])->rowCount() > 0;
    }

    public function poslednjiId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
