<div class="admin-panel">
    <div class="admin-panel-vrh">
        <h2>Upiti klijenata</h2>
        <div class="filteri-statusa">
            <a href="<?= url('admin/upiti') ?>" class="filter <?= $izabrani === null ? 'aktivan' : '' ?>">
                Svi
            </a>
            <?php
            $nazivi = ['nov' => 'Novi', 'u_obradi' => 'U obradi', 'zavrsen' => 'Završeni', 'odbijen' => 'Odbijeni'];
            foreach ($nazivi as $kljuc => $naziv):
                ?>
                <a href="<?= url('admin/upiti?status=' . $kljuc) ?>" class="filter <?= $izabrani === $kljuc ? 'aktivan' : '' ?>">
                    <?= e($naziv) ?> <span class="brojka"><?= (int) $brojevi[$kljuc] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($upiti)): ?>
        <p class="admin-prazno">Nema upita za izabrani filter.</p>
    <?php else: ?>
        <div class="tabela-okvir">
            <table class="admin-tabela tabela-upita">
                <thead>
                    <tr>
                        <th>Klijent</th>
                        <th>Kontakt</th>
                        <th>Traži</th>
                        <th>Poruka</th>
                        <th>Datum</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upiti as $u): ?>
                        <tr data-upit="<?= (int) $u['id'] ?>">
                            <td>
                                <strong><?= e($u['ime']) ?></strong>
                                <?php if (!empty($u['procena'])): ?>
                                    <small class="d-block text-muted">procena: <?= e(cena($u['procena'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="mailto:<?= e($u['email']) ?>"><?= e($u['email']) ?></a>
                                <?php if (!empty($u['telefon'])): ?>
                                    <small class="d-block"><a href="tel:<?= e($u['telefon']) ?>"><?= e($u['telefon']) ?></a></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e($u['usluga_naziv'] ?? '—') ?>
                                <?php if (!empty($u['kvadratura'])): ?>
                                    <small class="d-block text-muted"><?= (int) $u['kvadratura'] ?> m&sup2;</small>
                                <?php endif; ?>
                            </td>
                            <td class="celija-poruka">
                                <span class="poruka-kratka"><?= e(skrati((string) $u['poruka'], 70)) ?></span>
                                <button type="button" class="veza-dugme" data-puna-poruka="<?= e($u['poruka']) ?>"
                                        data-klijent="<?= e($u['ime']) ?>">
                                    prikaži
                                </button>
                            </td>
                            <td><?= e(datum($u['kreiran'])) ?></td>
                            <td>
                                <!-- Promena statusa ide preko web servisa, bez osvezavanja strane -->
                                <select class="izbor-statusa stanje-<?= e($u['status']) ?>" data-id="<?= (int) $u['id'] ?>">
                                    <?php foreach (Upit::STATUSI as $s): ?>
                                        <option value="<?= e($s) ?>" <?= $u['status'] === $s ? 'selected' : '' ?>>
                                            <?= e(ucfirst(str_replace('_', ' ', $s))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Prozor sa punom porukom, popunjava ga JavaScript -->
<div class="modal fade" id="modalPoruke" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNaslov">Poruka</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
            </div>
            <div class="modal-body" id="modalTelo"></div>
        </div>
    </div>
</div>
