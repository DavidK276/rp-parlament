<?php
include('classes.php');
session_start();
include('constants.php');
include('functions.php');
head('Správa adminov');
include('navbar.php');
include('database.php');

if (isset($_SESSION[SESSION_USER_ROLE]) && $_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) {
    try {
        if (isset($_POST['toggle_bp']) && isset($_GET['admin_id'])) {
            $admin = new Admin($_GET['admin_id']);
            if ($admin->udaje->id_previerka != null) {
                $bezp_prev = new BezpecnostnaPrevierka($admin->udaje->id_previerka);
                $bezp_prev->update_platnost();
            }
        } else if (isset($_POST['submit_bp']) && isset($_GET['admin_id'])) {
            $admin = new Admin($_GET['admin_id']);
            if ($admin->udaje->id_previerka != null) {
                $bezp_prev = new BezpecnostnaPrevierka($admin->udaje->id_previerka);
                $bezp_prev->uroven = $_POST['uroven'];
                $bezp_prev->kto_udelil = $_SESSION[SESSION_USER]->id;
                $bezp_prev->update_uroven();
            } else {
                $bezp_prev = new BezpecnostnaPrevierka();
                $bezp_prev->uroven = $_POST['uroven'];
                $bezp_prev->kto_udelil = $_SESSION[SESSION_USER]->id;
                $bezp_prev->insert();
                $admin->udaje->id_previerka = $bezp_prev->id;
                $admin->udaje->update();
            }
        }
    } catch (UserNotFoundException) {
        http_response_code(404);
        display_error('Zadaný admin neexistuje.');
    } catch (AttributeException|UserExistsException) {
        http_response_code(400);
        display_error('Chybná požiadavka');
    }
    if (isset($_POST['submit'])) {
        try {
            $admin = new Admin($_POST['admin_id']);
            $admin->udaje->email = $_POST['email'];
            $name = explode(' ', $_POST['cele_meno']);
            $admin->udaje->meno = $name[0];
            $admin->udaje->priezvisko = $name[1];
            $admin->udaje->titul = $_POST['titul'];
            $admin->udaje->adresa = $_POST['adresa'];
            $admin->udaje->update();
            $result = SUCCESS;
        } catch (AttributeException|UserNotFoundException) {
            http_response_code(400);
            display_error('Chybná požiadavka');
        } catch (UserExistsException) {
            $result = ERROR_USER_EXISTS;
        }
    }
    if (isset($_POST['delete'])) {
        try {
            $admin = new Admin($_POST['delete_id']);
            $admin->delete();
        } catch (UserNotFoundException) {
            http_response_code(404);
            display_error('Zadaný admin neexistuje.');
        } finally {
            header('location:admins.php');
        }
    } else if (isset($_GET['admin_id'])) {
        try {
            $admin = new Admin($_GET['admin_id']);
            if ($admin->udaje->id_previerka != null) $bezp_prev = new BezpecnostnaPrevierka($admin->udaje->id_previerka); ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex pb-4 align-items-center">
                            <h2>Stránka admin</h2>
                            <a href="admins.php" class="px-3" style="text-decoration: none">&lt;&lt;&nbsp;naspäť</a>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Meno a priezvisko:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4">
                                    <?= $admin->udaje->meno . ' ' . $admin->udaje->priezvisko ?></div>
                                <h6>Email:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4"><?= $admin->udaje->email ?></div>
                            </div>
                            <div class="col-md-4">
                                <h6>Titul:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4"><?= $admin->udaje->titul ?: '-' ?></div>
                                <h6>Bezpečnostná previerka:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4"><?= ($bezp_prev->uroven ?? '-');
                                    if (isset($bezp_prev)) echo $bezp_prev->platnost ? ' (platná)' : ' (neplatná)'; ?></div>
                            </div>
                            <div class="col-md-4">
                                <h6>Adresa:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4"><?= $admin->udaje->adresa ?></div>
                                <h6>BP udelil:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4">
                                    <?php if (isset($bezp_prev)) {
                                        $udelil = new Admin($bezp_prev->kto_udelil);
                                        echo $udelil->udaje->meno . ' ' . $udelil->udaje->priezvisko;
                                    } else echo '-'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" data-bs-toggle="collapse"
                                data-bs-target="#form_edit">Upraviť
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="collapse"
                                data-bs-target="#form_previerka">Spravovať BP
                        </button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="delete_id" value="<?= $_GET['admin_id'] ?>">
                            <button type="submit" class="btn btn-danger" name="delete">Vymazať</button>
                        </form>
                        <?php if (isset($result)) {
                            if ($result == SUCCESS) echo '<p class="d-inline mx-2 text-success">Admin upravený</p>';
                            else if ($result == ERROR_USER_EXISTS) echo '<p class="d-inline mx-2 text-danger">Zadaný email sa už používa!</p>';
                            else if ($result == ERROR_UNKNOWN) echo '<p class="d-inline mx-2 text-danger">Neznáma chyba</p>';
                        } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <form method="post" class="needs-validation collapse" id="form_edit" novalidate>
                            <div class="my-3">
                                <label for="email" class="form-label">Email:
                                    <i class="material-icons"
                                       title="Email musí mať platný formát napr. jozko@example.com">help</i>
                                </label>

                                <input type="email" class="form-control" id="email" placeholder="Zadajte email"
                                       name="email" required value="<?= $admin->udaje->email ?>">
                                <div class="invalid-feedback">Zadajte platný email</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="titul" class="form-label">Titul:
                                        <i class="material-icons" title="Tituly používateľa, napr. Mgr.">help</i>
                                    </label>
                                    <input type="text" class="form-control" id="titul" placeholder="Zadajte titul"
                                           name="titul"
                                           value="<?= $admin->udaje->titul ?>">
                                </div>
                                <div class="col-md-9">
                                    <label for="meno_priezvisko" class="form-label">Meno a priezvisko:
                                        <i class="material-icons"
                                           title="Musí obsahovať presne dve slová po aspoň 3 znaky a nesmie byť dlhšie ako 60 znakov">help</i>
                                    </label>
                                    <input type="text" class="form-control" id="meno_priezvisko"
                                           placeholder="Zadajte meno a priezvisko" name="cele_meno"
                                           value="<?= $admin->udaje->meno . ' ' . $admin->udaje->priezvisko ?>"
                                           required>
                                    <div class="invalid-feedback" id="meno_feedback"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="adresa" class="form-label">Adresa:
                                    <i class="material-icons" title="Musí mať medzi 6 a 100 znakov">help</i>
                                </label>
                                <!--                    <input type="text" class="form-control" id="adresa" placeholder="Zadajte adresu"-->
                                <!--                           name="adresa" value="" required>-->
                                <textarea class="form-control" id="adresa" placeholder="Zadajte adresu"
                                          name="adresa" rows="5" required><?= $admin->udaje->adresa ?></textarea>
                                <div class="invalid-feedback" id="adresa_feedback"></div>
                            </div>
                            <input type="hidden" name="admin_id" value="<?= $_GET['admin_id'] ?>">
                            <button type="submit" name="submit" class="btn btn-primary">Potvrdiť</button>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <form method="post" class="needs-validation collapse" id="form_previerka" novalidate>
                            <div class="my-3">
                                <label for="uroven" class="form-label"><b class="text-danger">*</b>&nbsp;Úroveň:
                                    <i class="material-icons" title="Určuje úroveň BP">help</i>
                                </label>
                                <div id="uroven">
                                    <?php
                                    $previerka = $bezp_prev ?? new BezpecnostnaPrevierka();
                                    foreach ($previerka->vsetky_urovne as $ur) { ?>
                                        <input type="radio" name="uroven" id="uroven_admin" value="<?= $ur ?>" required
                                               aria-selected="true" <?php if ($ur == $previerka->uroven) echo 'checked' ?>>
                                        <label for="uroven_admin"><?= $ur ?></label>
                                    <?php } ?>
                                    <div class="invalid-feedback">Vyberte úroveň</div>
                                </div>
                            </div>
                            <button type="submit" name="submit_bp"
                                    class="btn btn-primary"><?= ($previerka->id > 0) ? 'Upraviť' : 'Udeliť' ?></button>
                            <?php if ($previerka->id > 0) { ?>
                                <button type="submit" name="toggle_bp"
                                        class="btn <?= $previerka->platnost ? 'btn-danger' : 'btn-success'
                                        ?>"><?= $previerka->platnost ? 'Zrušiť platnosť' : 'Obnoviť platnosť' ?></button>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php } catch (UserNotFoundException) {
            display_error('Zadaný admin sa nenašiel');
        }
    } else {
        $admini = get_all_admini($GLOBALS['mysqli']);
        $admini = partition($admini, 3); ?>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <ul class="list-group">
                        <?php foreach ($admini[0] as $posl) { ?>
                            <a href="?admin_id=<?= $posl['id'] ?>"
                               class="list-group-item"><?= join(' ', array_slice($posl, 1)) ?></a>
                        <?php } ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="list-group">
                        <?php foreach ($admini[1] as $posl) { ?>
                            <a href="?admin_id=<?= $posl['id'] ?>"
                               class="list-group-item"><?= join(' ', array_slice($posl, 1)) ?></a>
                        <?php } ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="list-group">
                        <?php foreach ($admini[2] as $posl) { ?>
                            <a href="?admin_id=<?= $posl['id'] ?>"
                               class="list-group-item"><?= join(' ', array_slice($posl, 1)) ?></a>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php
    }
} else {
    display_error('K tejto stránke nemáte prístup.');
}
include('footer.php'); ?>

<script>
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation');

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    let address_input = document.getElementById('adresa');
                    if (address_input.value !== '' && address_input.value.length < 6) {
                        address_input.setCustomValidity('Adresa musí mať aspoň 6 znakov');
                        document.getElementById('adresa_feedback').innerHTML = 'Adresa musí mať aspoň 6 znakov';
                    } else if (address_input.value !== '' && address_input.value.length > 100) {
                        address_input.setCustomValidity('Adresa musí mať najviac 100 znakov');
                        document.getElementById('adresa_feedback').innerHTML = 'Adresa musí mať najviac 100 znakov';
                    } else {
                        address_input.setCustomValidity('');
                        document.getElementById('adresa_feedback').innerHTML = 'Zadajte adresu';
                    }

                    let name_input = document.getElementById('meno_priezvisko');
                    if (name_input.value !== '' && !verify_name(name_input.value)) {
                        name_input.setCustomValidity('Meno a priezvisko musia obsahovať presne dve slová');
                        document.getElementById('meno_feedback').innerHTML = 'Meno a priezvisko musia obsahovať ' +
                            'presne dve slová dlhé aspoň 3 znaky';
                    } else {
                        name_input.setCustomValidity('');
                        document.getElementById('meno_feedback').innerHTML = 'Zadajte meno a priezvisko';
                    }
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
                form.addEventListener('input', function () {
                    form.classList.remove('was-validated');
                });
            });
    })()

    function verify_name(name) {
        let name_split = name.split(' ');
        if (name_split.length !== 2) return false;
        return name_split[0].length >= 3 && name_split[1].length >= 3;
    }
</script>