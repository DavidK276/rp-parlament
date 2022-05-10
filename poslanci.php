<?php
include('classes.php');
session_start();
include('constants.php');
include('functions.php');
head('Poslanci');
include('navbar.php');
include('database.php');
$poslanci = get_all_poslanci($mysqli);
$poslanci = partition($poslanci, 3);

if (isset($_POST['submit']) && $_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) {
//    $error = false;
//    if (empty($_POST['poslanec_id'])) $error = true;
//    else if (empty($_POST['email'])) $error = true;
//    else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $error = true;
//    else if (strlen($_POST['titul']) > 20) $error = true;
//    else if (empty($_POST['cele_meno']) || strlen($_POST['cele_meno']) > 60 || !verify_name($_POST['cele_meno'])) $error = true;
//    else if (empty($_POST['adresa']) || strlen($_POST['adresa']) > 100 || strlen($_POST['adresa']) < 6) $error = true;
//    if ($error) {
//        http_response_code(400);
//        display_error('Chybná požiadavka.');
//    } else {
//        // ak je vstup platny, vlozit do databazy
//        $poslanec = array();
//        $poslanec['id'] = sanitise($_POST['poslanec_id']);
//        $poslanec['email'] = sanitise(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
//        $poslanec['titul'] = sanitise($_POST['titul']);
//        $whole_name = sanitise($_POST['cele_meno']);
//        $poslanec['meno'] = explode(' ', $whole_name)[0];
//        $poslanec['priezvisko'] = explode(' ', $whole_name)[1];
//        $poslanec['adresa'] = sanitise($_POST['adresa']);
//        if (!isset($_POST['specializacia'])) $poslanec['specializacia'] = '';
//        else $poslanec['specializacia'] = implode(',', $_POST['specializacia']);
//        $result = update_poslanec($mysqli, $poslanec);
//    }
    $poslanec = new Poslanec($mysqli, $_POST['poslanec_id']);
    $poslanec->udaje->email = $_POST['email'];
    $name = explode(' ', $_POST['cele_meno']);
    $poslanec->udaje->meno = $name[0];
    $poslanec->udaje->priezvisko = $name[1];
    $poslanec->udaje->titul = $_POST['titul'];
    $poslanec->udaje->adresa = $_POST['adresa'];
    $poslanec->specializacia = $_POST['specializacia'];
    try {
        $poslanec->update();
        $result = SUCCESS;
    }
    catch (AttributeException | UserNotFoundException) {
        http_response_code(400);
        display_error('Chybná požiadavka');
    }
    catch (UserExistsException) {
        $result = ERROR_USER_EXISTS;
    }
}

if (isset($_GET['poslanec_id'])) {
    // TODO: pridat tlacidlo na navrat na zoznam poslancov, t. j. unsetnut get parameter
    try {
        $poslanec = new Poslanec($mysqli, $_GET['poslanec_id']);
        if ($poslanec->udaje->id_previerka != null) $bezp_prev = new BezpecnostnaPrevierka($mysqli, $poslanec->udaje->id_previerka); ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2>Stránka poslanca</h2>
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Meno a priezvisko:</h6>
                            <div class="bg-secondary bg-opacity-25 container mb-4">
                                <?= $poslanec->udaje->meno . ' ' . $poslanec->udaje->priezvisko ?></div>
                            <h6>Email:</h6>
                            <div class="bg-secondary bg-opacity-25 container mb-4"><?= $poslanec->udaje->email ?></div>
                        </div>
                        <div class="col-md-4">
                            <h6>Titul:</h6>
                            <div class="bg-secondary bg-opacity-25 container mb-4"><?= $poslanec->udaje->titul ?: '-' ?></div>
                            <?php if ($_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) { ?>
                                <h6>Bezpečnostná previerka:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4"><?= ($bezp_prev->uroven ?? '-');
                                    if (isset($bezp_prev)) echo $bezp_prev->platnost ? ' (platná)' : ' (neplatná)'; ?></div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4">
                            <h6>Adresa:</h6>
                            <div class="bg-secondary bg-opacity-25 container mb-4"><?= $poslanec->udaje->adresa ?></div>
                            <?php if ($_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) { ?>
                                <h6>BP udelil:</h6>
                                <div class="bg-secondary bg-opacity-25 container mb-4">
                                    <?php if (isset($bezp_prev)) {
                                        $udelil = new Admin($mysqli, $bezp_prev->kto_udelil);
                                        echo $udelil->udaje->meno . ' ' . $udelil->udaje->priezvisko;
                                    } else echo '-'; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) { ?>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" data-bs-toggle="collapse"
                                data-bs-target="#form_edit">Upraviť
                        </button>
                        <?php if (isset($result)) {
                            if ($result == SUCCESS) echo '<p class="d-inline mx-2 text-success">Poslanec upravený</p>';
                            else if ($result == ERROR_USER_EXISTS) echo '<p class="d-inline mx-2 text-danger">Zadaný email sa už používa!</p>';
                            else if ($result == ERROR_UNKNOWN) echo '<p class="d-inline mx-2 text-danger">Neznáma chyba</p>';
                        }?>
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
                                       name="email"
                                       value="<?= $poslanec->udaje->email ?>">
                                <div class="invalid-feedback">Zadajte platný email</div>
                            </div>
                            <div class="mb-3">
                                <label for="hidden_field" class="form-label">Špecializácia:
                                    <i class="material-icons" title="Špecializácie používateľa">help</i>
                                </label>
                                <input type="hidden" id="hidden_field" value="">
                                <div class="form-check">
                                    <?php foreach (get_spec_values($mysqli) as $spec) {
                                        $l = strtolower($spec);
                                        $l = explode(' ', $l)[0]; ?>
                                        <input class="form-check-input" type="checkbox" value="<?= $spec ?>"
                                               id="sp_<?= $l ?>"
                                               name="specializacia[]" <?php if (in_array($spec, $poslanec->specializacia)) echo ' checked' ?>>
                                        <label class="form-check-label" for="sp_<?= $l ?>"><?= $spec ?></label><br>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="titul" class="form-label">Titul:
                                        <i class="material-icons" title="Tituly používateľa, napr. Mgr.">help</i>
                                    </label>
                                    <input type="text" class="form-control" id="titul" placeholder="Zadajte titul"
                                           name="titul"
                                           value="<?= $poslanec->udaje->titul ?>">
                                </div>
                                <div class="col-md-9">
                                    <label for="meno_priezvisko" class="form-label">Meno a priezvisko:
                                        <i class="material-icons"
                                           title="Musí obsahovať presne dve slová po aspoň 3 znaky a nesmie byť dlhšie ako 60 znakov">help</i>
                                    </label>
                                    <input type="text" class="form-control" id="meno_priezvisko"
                                           placeholder="Zadajte meno a priezvisko" name="cele_meno"
                                           value="<?= $poslanec->udaje->meno . ' ' . $poslanec->udaje->priezvisko ?>">
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
                                          name="adresa" rows="5"><?= $poslanec->udaje->adresa ?></textarea>
                                <div class="invalid-feedback" id="adresa_feedback"></div>
                            </div>
                            <!--                            <div class="row mb-3">-->
                            <!--                                <div class="col-md-6">-->
                            <!--                                    <label for="pwd" class="form-label"><b class="text-danger">*</b>&nbsp;Heslo:-->
                            <!--                                        <i class="material-icons" title="Heslá sa musia zhodovať">help</i>-->
                            <!--                                    </label>-->
                            <!--                                    <input type="password" class="form-control" id="pwd" placeholder="Vytvorte heslo" name="heslo0"-->
                            <!--                                           value="" required>-->
                            <!--                        <div class="invalid-feedback">Zadajte heslo</div>-->
                            <!--                                </div>-->
                            <!--                                <div class="col-md-6">-->
                            <!--                                    <label for="pwd_rep" class="form-label invisible">Zopakovať heslo:</label>-->
                            <!--                                    <input type="password" class="form-control" id="pwd_rep" placeholder="Zopakovať heslo"-->
                            <!--                                           name="heslo1" value="" required>-->
                            <!--                                    <div class="invalid-feedback" id="pwd_feedback"></div>-->
                            <!--                                </div>-->
                            <!--                            </div>-->
                            <input type="hidden" name="poslanec_id" value="<?= $_GET['poslanec_id'] ?>">
                            <button type="submit" name="submit" class="btn btn-primary">Potvrdiť</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php }
    catch (UserNotFoundException) {
        display_error('Zadaný poslanec sa nenašiel');
    }
} else { ?>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <ul class="list-group">
                    <?php foreach ($poslanci[0] as $posl) { ?>
                        <a href="?poslanec_id=<?= $posl['id'] ?>"
                           class="list-group-item"><?= join(' ', array_slice($posl, 1)) ?></a>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-group">
                    <?php foreach ($poslanci[1] as $posl) { ?>
                        <a href="?poslanec_id=<?= $posl['id'] ?>"
                           class="list-group-item"><?= join(' ', array_slice($posl, 1)) ?></a>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-group">
                    <?php foreach ($poslanci[2] as $posl) { ?>
                        <a href="?poslanec_id=<?= $posl['id'] ?>"
                           class="list-group-item"><?= join(' ', array_slice($posl, 1)) ?></a>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

<?php }
include('footer.php'); ?>

<script>
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation');

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
