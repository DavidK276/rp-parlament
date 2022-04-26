<?php
session_start();
// TODO: zakazat pristup neopravnenym ludom
include('constants.php');
include('functions.php');
include('database.php');
head('Pridať používateľa');
include('navbar.php'); ?>

<?php if (isset($_POST['submit'])) {
    $errors = array();
//    $email = sanitise($_POST['email']);
//    $role = $_POST['rola'];
//    $title = sanitise($_POST['titul']);
//    $whole_name = sanitise($_POST['cele_meno']);
//    $first_name = explode(' ', $whole_name)[0];
//    $last_name = explode(' ', $whole_name)[1];
//    $address = sanitise($_POST['adresa']);
//    $password = $_POST['heslo0'];
//    $password_repeat = $_POST['heslo1'];
//
//    // kontrola vstupu
//    if (empty($email)) $errors['email'] = "Musíte zadať email";
//    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Zadaný email má zlý formát";
//    if (empty($role)) $errors['role'] = "Musíte zvoliť rolu";
//    if (strlen($title) > 20) $errors['title'] = "Titul musí mať najviac 20 znakov";
//    if (empty($first_name)) $errors['first_name'] = "Musíte zadať meno";
//    else if (strlen($first_name) > 30) $errors['first_name'] = "Meno musí mať najviac 30 znakov";
//    if (empty($last_name)) $errors['last_name'] = "Musíte zadať priezvisko";
//    else if (strlen($last_name) > 30) $errors['last_name'] = "Priezvisko musí mať najviac 30 znakov";
//    if (empty($address)) $errors['address'] = "Musíte zadať adresu";
//    else if (strlen($address) > 50) $errors['address'] = "Adresa musí mať najviac 50 znakov";
//    if (empty($password)) $errors['password'] = "Zadajte heslo";
//    else if ($password != $password_repeat) $errors['password'] = "Heslá sa nezhodujú";

    if (empty($errors)) {
        // ak je vstup platny, vlozit do databazy
        if ($_POST['rola'] == 'poslanec') {
            // TODO: vyrobit pole poslanec
            $poslanec = [];
            $result = insert_poslanec($mysqli, $poslanec);
        } else if ($_POST['rola'] == 'admin') {
            // TODO: vyrobit pole admin
            $admin = [];
            $result = insert_admin($mysqli, $admin);
        }
    }
} ?>

<div class="row">
    <div class="col-md-4">
        <div class="container">
            <h2>Nový používateľ</h2>
            <form method="post" class="needs-validation" id="form_add_user" novalidate>
                <div class="my-3">
                    <label for="email" class="form-label">Email:</label>

                    <input type="email" class="form-control" id="email" placeholder="Zadajte email" name="email"
                           value="" required>
                    <div class="invalid-feedback">Zadajte platný email</div>
                </div>
                <div class="mb-3">
                    <label for="rola" class="form-label">Rola:</label>
                    <div id="rola">
                        <input type="radio" name="rola" id="rola_admin" value="admin" required aria-selected="true"
                               checked>
                        <label for="rola_admin">Administrátor</label>
                        <input type="radio" name="rola" id="rola_poslanec" value="poslanec" required>
                        <label for="rola_poslanec">Poslanec</label>
                        <div class="invalid-feedback">Vyberte rolu</div>
                    </div>
                </div>
                <div class="mb-3" hidden>
                    <label for="titul" class="form-label">Titul:</label>
                    <input type="text" class="form-control" id="titul" placeholder="Zadajte titul" name="titul"
                           value="">
                </div>
                <div class="mb-3" hidden>
                    <label for="hidden_field" class="form-label">Špecializácia:</label>
                    <input type="hidden" id="hidden_field" value="">
                    <div class="form-check">
                        <?php foreach (get_spec_values($mysqli) as $spec) {
                            $l = strtolower($spec);
                            $l = explode(' ', $l)[0];
                            echo "<input class=\"form-check-input\" type=\"checkbox\" value=\"$l\" id=\"sp_$l\" name=\"specializacia\">
                                    <label class=\"form-check-label\" for=\"sp_$l\">$spec</label><br>";
                        } ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="meno_priezvisko" class="form-label">Meno a priezvisko:</label>
                    <input type="text" class="form-control" id="meno_priezvisko"
                           placeholder="Zadajte meno a priezvisko" name="cele_meno" value="" required>
                    <div class="invalid-feedback" id="meno_feedback"></div>
                </div>
                <div class="mb-3">
                    <label for="adresa" class="form-label">Adresa:</label>
                    <input type="text" class="form-control" id="adresa" placeholder="Zadajte adresu"
                           name="adresa" value="" required>
                    <div class="invalid-feedback" id="adresa_feedback"></div>
                </div>
                <div class="mb-1">
                    <label for="pwd" class="form-label">Heslo:</label>
                    <input type="password" class="form-control" id="pwd" placeholder="Vytvorte heslo" name="heslo0"
                           value="" required>
                    <!--                        <div class="invalid-feedback">Zadajte heslo</div>-->
                </div>
                <div class="mb-3">
                    <label for="pwd_rep" class="form-label" hidden>Zopakovať heslo:</label>
                    <input type="password" class="form-control" id="pwd_rep" placeholder="Zopakovať heslo"
                           name="heslo1" value="" required>
                    <div class="invalid-feedback" id="pwd_feedback"></div>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Pridať používaťeľa</button>
                <?php if (isset($errors) && empty($errors)) echo '<p class="d-inline mx-2 text-success">Používateľ pridaný!</p>'; ?>
            </form>
        </div>
    </div>
    <div class="col-md-4"></div>
    <div class="col-md-4"></div>
</div>

<?php include('footer.php'); ?>
<script>
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation');

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    let pwd_rep_input = document.getElementById('pwd_rep');
                    let pwd_input = document.getElementById('pwd');
                    if (pwd_input.value !== pwd_rep_input.value && pwd_input.value !== '' && pwd_rep_input.value !== '') {
                        pwd_rep_input.setCustomValidity('Heslá sa musia zhodovať');
                        pwd_input.setCustomValidity('Heslá sa musia zhodovať');
                        document.getElementById('pwd_feedback').innerHTML = 'Heslá sa musia zhodovať';
                    } else {
                        pwd_rep_input.setCustomValidity('');
                        pwd_input.setCustomValidity('');
                        document.getElementById('pwd_feedback').innerHTML = 'Zadajte heslo';
                    }

                    let address_input = document.getElementById('adresa');
                    if (address_input.value !== '' && address_input.value.length < 6) {
                        address_input.setCustomValidity('Adresa musí mať aspoň 6 znakov');
                        document.getElementById('adresa_feedback').innerHTML = 'Adresa musí mať aspoň 6 znakov';
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
                    form.classList.add('was-validated')
                }, false)
            });


        document.getElementById('form_add_user').addEventListener('input', function () {
            document.getElementById('form_add_user').classList.remove('was-validated');
        });

        document.getElementById('rola_admin').addEventListener('click', function () {
            document.getElementById('titul').parentElement.setAttribute('hidden', '');
            document.getElementById('hidden_field').parentElement.setAttribute('hidden', '');
        });

        document.getElementById('rola_poslanec').addEventListener('click', function () {
            document.getElementById('titul').parentElement.removeAttribute('hidden');
            document.getElementById('hidden_field').parentElement.removeAttribute('hidden');
        });
    })()

    function verify_name(name) {
        let name_split = name.split(' ');
        if (name_split.length !== 2) return false;
        return name_split[0].length >= 3 && name_split[1].length >= 3;
    }
</script>
