<div class="admin-panel">
    <div class="admin-panel-vrh">
        <h2>Radovi u galeriji</h2>
        <a href="<?= url('admin/radovi/nov') ?>" class="dugme dugme-glavno">
            <i class="bi bi-plus-lg"></i> Nov rad
        </a>
    </div>

    <?php if (empty($radovi)): ?>
        <p class="admin-prazno">Još uvek nema unetih radova.</p>
    <?php else: ?>
        <div class="tabela-okvir">
            <table class="admin-tabela">
                <thead>
                    <tr>
                        <th style="width:80px">Slika</th>
                        <th>Naziv</th>
                        <th>Usluga</th>
                        <th>Lokacija</th>
                        <th>m&sup2;</th>
                        <th>Godina</th>
                        <th>Stanje</th>
                        <th class="desno">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($radovi as $r): ?>
                        <tr data-rad="<?= (int) $r['id'] ?>">
                            <td>
                                <img src="<?= slika($r['slika']) ?>" alt="" class="minijatura">
                            </td>
                            <td>
                                <strong><?= e($r['naziv']) ?></strong>
                                <?php if ((int) $r['izdvojen'] === 1): ?>
                                    <span class="stanje stanje-izdvojen">izdvojen</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($r['usluga_naziv'] ?? '—') ?></td>
                            <td><?= e($r['lokacija']) ?></td>
                            <td><?= $r['kvadratura'] !== null ? (int) $r['kvadratura'] : '—' ?></td>
                            <td><?= $r['godina'] !== null ? (int) $r['godina'] . '.' : '—' ?></td>
                            <td>
                                <span class="stanje <?= (int) $r['objavljen'] === 1 ? 'stanje-zavrsen' : 'stanje-odbijen' ?>">
                                    <?= (int) $r['objavljen'] === 1 ? 'objavljen' : 'skriven' ?>
                                </span>
                            </td>
                            <td class="desno">
                                <a href="<?= url('radovi/' . (int) $r['id']) ?>" class="ikona-dugme" target="_blank" title="Pogledaj na sajtu">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= url('admin/radovi/izmeni/' . (int) $r['id']) ?>" class="ikona-dugme" title="Izmeni">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <!-- Brisanje ide preko web servisa DELETE api/radovi/{id} -->
                                <button type="button" class="ikona-dugme opasno" title="Obriši"
                                        data-obrisi-rad="<?= (int) $r['id'] ?>"
                                        data-naziv="<?= e($r['naziv']) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
