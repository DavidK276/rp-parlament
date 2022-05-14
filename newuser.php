<?php
include('classes.php');
session_start();
include('constants.php');
include('functions.php');
include('database.php');
head('Pridať používateľa');
include('navbar.php'); ?>

<?php
if (isset($_SESSION[SESSION_USER]) && $_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) {
    if (isset($_POST['submit']) && isset($_POST['rola'])) {
        if ($_POST['rola'] == ROLE_POSLANEC) {
            $poslanec = new Poslanec();
            set_user_attributes($poslanec);
            $poslanec->specializacia = $_POST['specializacia'] ?? '';
            try {
                $poslanec->insert($_POST['heslo0'] ?? '');
                $result = SUCCESS;
            } catch (AttributeException) {
                http_response_code(400);
                display_error('Chybná požiadavka');
            } catch (UserExistsException) {
                http_response_code(400);
                $result = ERROR_USER_EXISTS;
            }
        } else if ($_POST['rola'] == ROLE_ADMIN) {
            $admin = new Admin();
            set_user_attributes($admin);
            try {
                $admin->insert($_POST['heslo0'] ?? '');
                $result = SUCCESS;
            } catch (AttributeException) {
                http_response_code(400);
                display_error('Chybná požiadavka');
            } catch (UserExistsException) {
                http_response_code(400);
                $result = ERROR_USER_EXISTS;
            }
        }
    } ?>
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <h2>Nový používateľ</h2>
                <form method="post" class="needs-validation" id="form_add_user" novalidate>
                    <div class="my-3">
                        <label for="email" class="form-label"><b class="text-danger">*</b>&nbsp;Email:
                            <i class="material-icons"
                               title="Email musí mať platný formát napr. jozko@example.com">help</i>
                        </label>

                        <input type="email" class="form-control" id="email" placeholder="Zadajte email" name="email"
                               value="" required>
                        <div class="invalid-feedback">Zadajte platný email</div>
                    </div>
                    <div class="mb-3">
                        <label for="rola" class="form-label"><b class="text-danger">*</b>&nbsp;Rola:
                            <i class="material-icons" title="Určuje rolu nového používateľa">help</i>
                        </label>
                        <div id="rola">
                            <nobr>
                                <input type="radio" name="rola" id="rola_admin" value="<?= ROLE_ADMIN ?>" required
                                       aria-selected="true"
                                       checked>
                                <label for="rola_admin">Administrátor</label>
                            </nobr>
                            <nobr>
                                <input type="radio" name="rola" id="rola_poslanec" value="<?= ROLE_POSLANEC ?>"
                                       required>
                                <label for="rola_poslanec">Poslanec</label>
                            </nobr>
                            <div class="invalid-feedback">Vyberte rolu</div>
                        </div>
                    </div>
                    <div class="mb-3" hidden>
                        <label for="hidden_field" class="form-label">Špecializácia:
                            <i class="material-icons" title="Špecializácie používateľa">help</i>
                        </label>
                        <input type="hidden" id="hidden_field" value="">
                        <div class="form-check">
                            <?php foreach (get_spec_values($GLOBALS['mysqli']) as $spec) {
                                $l = strtolower($spec);
                                $l = explode(' ', $l)[0];
                                echo "<input class=\"form-check-input\" type=\"checkbox\" value=\"$spec\" id=\"sp_$l\" name=\"specializacia[]\">
                                    <label class=\"form-check-label\" for=\"sp_$l\">$spec</label><br>";
                            } ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="titul" class="form-label">Titul:
                            <i class="material-icons" title="Tituly používateľa, napr. Mgr.">help</i>
                        </label>
                        <input type="text" class="form-control" id="titul" placeholder="Zadajte titul" name="titul"
                               value="">
                    </div>
                    <div class="mb-3">
                        <label for="meno_priezvisko" class="form-label"><b class="text-danger">*</b>&nbsp;Meno a
                            priezvisko:
                            <i class="material-icons"
                               title="Musí obsahovať presne dve slová po aspoň 3 znaky a nesmie byť dlhšie ako 60 znakov">help</i>
                        </label>
                        <input type="text" class="form-control" id="meno_priezvisko"
                               placeholder="Zadajte meno a priezvisko" name="cele_meno" value="" required>
                        <div class="invalid-feedback" id="meno_feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="adresa" class="form-label"><b class="text-danger">*</b>&nbsp;Adresa:
                            <i class="material-icons" title="Musí mať medzi 6 a 100 znakov">help</i>
                        </label>
                        <!--                    <input type="text" class="form-control" id="adresa" placeholder="Zadajte adresu"-->
                        <!--                           name="adresa" value="" required>-->
                        <textarea class="form-control" id="adresa" placeholder="Zadajte adresu"
                                  name="adresa" rows="5" required></textarea>
                        <div class="invalid-feedback" id="adresa_feedback"></div>
                    </div>
                    <div class="mb-1">
                        <label for="pwd" class="form-label"><b class="text-danger">*</b>&nbsp;Heslo:
                            <i class="material-icons" title="Heslá sa musia zhodovať a musia byť kratšie ako 72 znakov">help</i>
                        </label>
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
                    <?php if (isset($result)) {
                        if ($result == SUCCESS) echo '<p class="d-inline mx-2 text-success">Používateľ pridaný</p>';
                        else if ($result == ERROR_USER_EXISTS) echo '<p class="d-inline mx-2 text-danger">Zadaný email sa už používa!</p>';
                        else if ($result == ERROR_UNKNOWN) echo '<p class="d-inline mx-2 text-danger">Neznáma chyba</p>';
                    } ?>
                </form>
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4"></div>
        </div>
    </div>

    <script>
        (function () {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation');

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        let pwd_rep_input = document.getElementById('pwd_rep');
                        let pwd_input = document.getElementById('pwd');
                        if (pwd_input.value !== pwd_rep_input.value && pwd_input.value !== '' && pwd_rep_input.value !== '') {
                            pwd_rep_input.setCustomValidity('Heslá sa musia zhodovať');
                            pwd_input.setCustomValidity('Heslá sa musia zhodovať');
                            document.getElementById('pwd_feedback').innerHTML = 'Heslá sa musia zhodovať';
                        }
                        else if (pwd_input.value.length >= 72) {
                            pwd_rep_input.setCustomValidity('Heslo nesmie byť dlhšie ako 72 znakov');
                            pwd_input.setCustomValidity('Heslo nesmie byť dlhšie ako 72 znakov');
                            document.getElementById('pwd_feedback').innerHTML = 'Heslo nesmie byť dlhšie ako 72 znakov';
                        }
                        else {
                            pwd_rep_input.setCustomValidity('');
                            pwd_input.setCustomValidity('');
                            document.getElementById('pwd_feedback').innerHTML = 'Zadajte heslo';
                        }

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
                        form.addEventListener('input', function () {
                            form.classList.remove('was-validated');
                        });
                    }, false)
                });

            document.getElementById('rola_admin').addEventListener('click', function () {
                document.getElementById('hidden_field').parentElement.setAttribute('hidden', '');
            });

            document.getElementById('rola_poslanec').addEventListener('click', function () {
                document.getElementById('hidden_field').parentElement.removeAttribute('hidden');
            });
        })();

        function verify_name(name) {
            let name_split = name.split(' ');
            if (name_split.length !== 2) return false;
            return name_split[0].length >= 3 && name_split[1].length >= 3;
        }
    </script>
<?php } else {
    display_error('K tejto stránke nemáte prístup.');
}

include('footer.php');