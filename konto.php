<?php
session_start();
include('constants.php');
include('functions.php');
include('database.php');
head(isset($_SESSION[SESSION_USER]) ? 'Konto' : 'Prihlásenie');
// overenie udajov treba robit skor ako sa spusti navbar, aby sa prihlasenie hned prejavilo
if (isset($_POST['login'])) {
    $verify = verify_user($mysqli, $_POST['email'], $_POST['pswd']);
    if ($verify == ROLE_POSLANEC) {
        // spravne udaje
        $_SESSION[SESSION_USER] = select_poslanec($mysqli, $_POST['email']);
        $_SESSION[SESSION_USER_ROLE] = ROLE_POSLANEC;
    } else if ($verify == ROLE_ADMIN) {
        $_SESSION[SESSION_USER] = select_admin($mysqli, $_POST['email']);
        $_SESSION[SESSION_USER_ROLE] = ROLE_ADMIN;
    } else {
        // nespravne udaje
        $password_incorrect = true;
    }
}
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
                    <?php $user = $_SESSION[SESSION_USER]; ?>
                    <h6>Meno a priezvisko:</h6>
                    <div class="bg-secondary bg-opacity-25 container"><?= $user['meno'] . ' ' . $user['priezvisko'] ?></div>
                </div>
                <?php if ($_SESSION[SESSION_USER_ROLE] == ROLE_POSLANEC) { ?>
                    <div class="col-md-4">
                        <h6>Titul:</h6>
                        <div class="bg-secondary bg-opacity-25 container"><?= $user['titul'] ?></div>
                    </div>
                <?php } ?>
                <div class="col-md-4">
                    <h6>Adresa:</h6>
                    <div class="bg-secondary bg-opacity-25 container"><?= $user['adresa'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }

include('footer.php');
