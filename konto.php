<?php
include('classes.php');
session_start();
include('constants.php');
include('database.php');
include('functions.php');
// overenie udajov treba robit skor ako sa spusti navbar, aby sa prihlasenie hned prejavilo
if (isset($_POST['login'])) {
    $admin = new Admin();
    if ($admin->login($_POST['email'], $_POST['pswd'])) {
        $_SESSION[SESSION_USER] = $admin;
        $_SESSION[SESSION_USER_ROLE] = ROLE_ADMIN;
    } else {
        unset($admin);
        $poslanec = new Poslanec();
        if ($poslanec->login($_POST['email'], $_POST['pswd'])) {
            $_SESSION[SESSION_USER] = $poslanec;
            $_SESSION[SESSION_USER_ROLE] = ROLE_POSLANEC;
        } else $password_incorrect = true;
    }
} else if (isset($_SESSION[SESSION_USER]) && isset($_POST['change_pswd'])) {
    $user = $_SESSION[SESSION_USER];
    if ($user->login($user->udaje->email, $_POST['stare_heslo'])) {
        $user->update_heslo($_POST['heslo0']);
        $password_update = true;
    } else $password_update = false;
}
head(isset($_SESSION[SESSION_USER]) ? 'Konto' : 'Prihlásenie');
include('navbar.php');

if (!isset($_SESSION[SESSION_USER])) { ?>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h2>Prihlásenie do parlamentu</h2>
                <?php if (isset($password_incorrect) && $password_incorrect) echo '<p class="text-danger m-0">Nesprávne meno alebo heslo.</p>' ?>
                <form method="post" class="needs-validation">
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" placeholder="Zadajte email" name="email"
                               value="<?php if (DEBUG) echo 'test@example.com'?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="pwd" class="form-label">Heslo:</label>
                        <input type="password" class="form-control" id="pwd" placeholder="Zadajte heslo" name="pswd"
                               value="<?php if (DEBUG) echo 'heslo123' ?>" required>
                    </div>
<!--                    <div class="form-check mb-3">-->
<!--                        <label class="form-check-label">-->
<!--                            <input class="form-check-input" type="checkbox" name="remember"> Zapamätať prihlásenie-->
<!--                        </label>-->
<!--                    </div>-->
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
                        if ($user->udaje->id_previerka != null) $bezp_prev = new BezpecnostnaPrevierka($user->udaje->id_previerka); ?>
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
                                $udelil = new Admin($bezp_prev->kto_udelil);
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
                <?php if (isset($password_update)) echo $password_update ?
                    '<p class="d-inline mx-2 text-success">Heslo zmenené!' :
                    '<p class="d-inline mx-2 text-danger">Nesprávne staré heslo!' . '</p>' ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form method="post" class="needs-validation collapse" id="form_heslo" novalidate>
                    <div class="row my-3">
                        <div class="col-md-4">
                            <label for="old_pwd" class="form-label"><b class="text-danger">*</b>&nbsp;Pôvodné
                                heslo:</label>
                            <input type="password" class="form-control" id="old_pwd" placeholder="Zadajte heslo"
                                   name="stare_heslo"
                                   value="" required>
                            <div class="invalid-feedback">Zadajte heslo</div>
                        </div>
                        <div class="col-md-4">
                            <label for="pwd" class="form-label"><b class="text-danger">*</b>&nbsp;Nové heslo:
                                <i class="material-icons" title="Heslá sa musia zhodovať">help</i>
                            </label>
                            <input type="password" class="form-control" id="pwd" placeholder="Vytvorte heslo"
                                   name="heslo0"
                                   value="" required>
                            <div class="invalid-feedback" id="pwd_feedback">Zadajte nové heslo</div>
                        </div>
                        <div class="col-md-4">
                            <label for="pwd_rep" class="form-label invisible">Zopakovať heslo:</label>
                            <input type="password" class="form-control" id="pwd_rep" placeholder="Zopakovať heslo"
                                   name="heslo1" value="" required>
                        </div>
                    </div>
                    <input type="hidden" name="poslanec_id" value="<?= $user->id ?>">
                    <button type="submit" name="change_pswd" class="btn btn-primary">Potvrdiť</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function () {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            let forms = document.querySelectorAll('.needs-validation');

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
                            document.getElementById('pwd_feedback').innerHTML = 'Zadajte nové heslo';
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
