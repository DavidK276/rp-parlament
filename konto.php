<?php
include('classes.php');
session_start();
include('constants.php');
include('functions.php');
include('database.php');
// overenie udajov treba robit skor ako sa spusti navbar, aby sa prihlasenie hned prejavilo
if (isset($_POST['login'])) {
//    $verify = verify_user($mysqli, $_POST['email'], $_POST['pswd']);
//    if ($verify == ROLE_POSLANEC) {
//        // spravne udaje
//        $_SESSION[SESSION_USER] = select_poslanec($mysqli, $_POST['email']);
//        $_SESSION[SESSION_USER_ROLE] = ROLE_POSLANEC;
//    } else if ($verify == ROLE_ADMIN) {
//        $_SESSION[SESSION_USER] = select_admin($mysqli, $_POST['email']);
//        $_SESSION[SESSION_USER_ROLE] = ROLE_ADMIN;
//    } else {
//        // nespravne udaje
//        $password_incorrect = true;
//    }
    $admin = new Admin($mysqli);
    if ($admin->login($_POST['email'], $_POST['pswd'])) {
        $_SESSION[SESSION_USER] = $admin;
        $_SESSION[SESSION_USER_ROLE] = ROLE_ADMIN;
    } else {
        unset($admin);
        $poslanec = new Poslanec($mysqli);
        if ($poslanec->login($_POST['email'], $_POST['pswd'])) {
            $_SESSION[SESSION_USER] = $poslanec;
            $_SESSION[SESSION_USER_ROLE] = ROLE_POSLANEC;
        } else $password_incorrect = true;
    }
}
head(isset($_SESSION[SESSION_USER]) ? 'Konto' : 'Prihlásenie');
include('navbar.php');

if (!isset($_SESSION[SESSION_USER])) { ?>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h2>Prihlásenie do parlamentu</h2>
                <?php if (isset($password_incorrect) && $password_incorrect) echo '<p class="text-danger m-0">Nesprávne meno alebo heslo.</p>' ?>
                <form method="post">
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" placeholder="Zadajte email" name="email"
                               value="test@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="pwd" class="form-label">Heslo:</label>
                        <input type="password" class="form-control" id="pwd" placeholder="Zadajte heslo" name="pswd"
                               value="heslo123">
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="remember"> Zapamätať prihlásenie
                        </label>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Prihlásiť</button>
                </form>
            </div>
        </div>
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
    </div>
<?php } else { ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Moje parlamentné konto</h2>
                <div class="row">
                    <div class="col-md-4">
                        <?php $user = $_SESSION[SESSION_USER];
                        if ($user->udaje->id_previerka != null) $bezp_prev = new BezpecnostnaPrevierka($mysqli, $user->udaje->id_previerka); ?>
                        <h6>Meno a priezvisko:</h6>
                        <div class="bg-secondary bg-opacity-25 container mb-4"><?= $user->udaje->meno . ' ' . $user->udaje->priezvisko ?></div>
                        <h6>Bezpečnostná previerka:</h6>
                        <div class="bg-secondary bg-opacity-25 container mb-4"><?= ($bezp_prev->uroven ?? '-');
                            if (isset($bezp_prev)) echo $bezp_prev->platnost ? ' (platná)' : ' (neplatná)'; ?></div>
                    </div>
                    <div class="col-md-4">
                        <h6>Titul:</h6>
                        <div class="bg-secondary bg-opacity-25 container mb-4"><?= $user->udaje->titul ?: '-' ?></div>
                        <h6>BP udelil:</h6>
                        <div class="bg-secondary bg-opacity-25 container mb-4">
                            <?php if (isset($bezp_prev)) {
                                $udelil = new Admin($mysqli, $bezp_prev->kto_udelil);
                                echo $udelil->udaje->meno . ' ' . $udelil->udaje->priezvisko;
                            } else echo '-'; ?></div>
                    </div>
                    <div class="col-md-4">
                        <h6>Adresa:</h6>
                        <div class="bg-secondary bg-opacity-25 container mb-4"><?= $user->udaje->adresa ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="button" class="btn btn-primary" data-bs-toggle="collapse"
                        data-bs-target="#form_heslo">Zmeniť heslo
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <form method="post" class="needs-validation collapse" id="form_heslo" novalidate>
                    <div class="row my-3">
                        <div class="col-md-6">
                            <label for="pwd" class="form-label"><b class="text-danger">*</b>&nbsp;Heslo:
                                <i class="material-icons" title="Heslá sa musia zhodovať">help</i>
                            </label>
                            <input type="password" class="form-control" id="pwd" placeholder="Vytvorte heslo"
                                   name="heslo0"
                                   value="" required>
                            <div class="invalid-feedback" id="pwd_feedback">Zadajte heslo</div>
                        </div>
                        <div class="col-md-6">
                            <label for="pwd_rep" class="form-label invisible">Zopakovať heslo:</label>
                            <input type="password" class="form-control" id="pwd_rep" placeholder="Zopakovať heslo"
                                   name="heslo1" value="" required>
                        </div>
                    </div>
                    <input type="hidden" name="poslanec_id" value="<?= $user->id ?>">
                    <button type="submit" name="submit" class="btn btn-primary">Potvrdiť</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function () {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');

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
                        } else {
                            pwd_rep_input.setCustomValidity('');
                            pwd_input.setCustomValidity('');
                            document.getElementById('pwd_feedback').innerHTML = 'Zadajte heslo';
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
    </script>
<?php }

include('footer.php');
