<div class="admin-panel">
    <div class="admin-panel-vrh">
        <h2>Usluge i cene</h2>
        <span class="admin-napomena">Klik na red otvara formu za izmenu</span>
    </div>

    <div class="tabela-okvir">
        <table class="admin-tabela">
            <thead>
                <tr>
                    <th style="width:52px"></th>
                    <th>Naziv</th>
                    <th>Kratak opis</th>
                    <th class="desno">Cena od</th>
                    <th>Redosled</th>
                    <th>Stanje</th>
                    <th class="desno">Radnje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usluge as $u): ?>
                    <tr>
                        <td><span class="admin-ikonica"><i class="bi <?= e($u['ikona']) ?>"></i></span></td>
                        <td>
                            <strong><?= e($u['naziv']) ?></strong>
                            <small class="d-block text-muted"><?= e($u['slug']) ?></small>
                        </td>
                        <td class="text-muted"><?= e(skrati($u['kratak_opis'], 60)) ?></td>
                        <td class="desno"><strong><?= e(cena($u['cena_od'])) ?></strong> <small>/ <?= e($u['jedinica']) ?></small></td>
                        <td><?= (int) $u['redosled'] ?></td>
                        <td>
                            <span class="stanje <?= (int) $u['aktivna'] === 1 ? 'stanje-zavrsen' : 'stanje-odbijen' ?>">
                                <?= (int) $u['aktivna'] === 1 ? 'aktivna' : 'skrivena' ?>
                            </span>
                        </td>
                        <td class="desno">
                            <button type="button" class="ikona-dugme" title="Izmeni"
                                    data-uredi-uslugu='<?= e(json_encode([
                                        'id'          => (int) $u['id'],
                                        'naziv'       => $u['naziv'],
                                        'ikona'       => $u['ikona'],
                                        'kratak_opis' => $u['kratak_opis'],
                                        'opis'        => $u['opis'],
                                        'cena_od'     => (float) $u['cena_od'],
                                        'jedinica'    => $u['jedinica'],
                                        'redosled'    => (int) $u['redosled'],
                                        'aktivna'     => (int) $u['aktivna'],
                                    ], JSON_UNESCAPED_UNICODE)) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Forma za izmenu usluge; podatke u nju upisuje JavaScript -->
<div class="modal fade" id="modalUsluge" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" method="post" action="<?= url('admin/usluge/sacuvaj') ?>">
            <?= Csrf::polje() ?>
            <input type="hidden" name="id" id="uslugaId">

            <div class="modal-header">
                <h5 class="modal-title">Izmena usluge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="oznaka" for="uslugaNaziv">Naziv <span>*</span></label>
                        <input type="text" class="polje" id="uslugaNaziv" name="naziv" required maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="oznaka" for="uslugaIkona">Ikona (Bootstrap Icons)</label>
                        <input type="text" class="polje" id="uslugaIkona" name="ikona" maxlength="60" placeholder="bi-tools">
                    </div>
                    <div class="col-md-4">
                        <label class="oznaka" for="uslugaCena">Cena od</label>
                        <input type="number" step="0.01" min="0" class="polje" id="uslugaCena" name="cena_od">
                    </div>
                    <div class="col-md-4">
                        <label class="oznaka" for="uslugaJedinica">Jedinica</label>
                        <input type="text" class="polje" id="uslugaJedinica" name="jedinica" maxlength="20" placeholder="m2">
                    </div>
                    <div class="col-md-4">
                        <label class="oznaka" for="uslugaRedosled">Redosled</label>
                        <input type="number" class="polje" id="uslugaRedosled" name="redosled">
                    </div>
                    <div class="col-12">
                        <label class="oznaka" for="uslugaKratak">Kratak opis</label>
                        <input type="text" class="polje" id="uslugaKratak" name="kratak_opis" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="oznaka" for="uslugaOpis">Detaljan opis</label>
                        <textarea class="polje" id="uslugaOpis" name="opis" rows="6"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="prekidac">
                            <input type="checkbox" name="aktivna" value="1" id="uslugaAktivna">
                            <span class="prekidac-telo"></span>
                            <span>Prikazuj na sajtu</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="dugme dugme-obrub-tamni" data-bs-dismiss="modal">Odustani</button>
                <button type="submit" class="dugme dugme-glavno"><i class="bi bi-check-lg"></i> Sačuvaj</button>
            </div>
        </form>
    </div>
</div>
