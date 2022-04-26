<?php
session_start();
include('constants.php');
include('functions.php');
include('database.php');
head(isset($_SESSION[SESSION_USER_ID]) ? 'Konto' : 'Prihlásenie');
// overenie udajov treba robit skor ako sa spusti navbar, aby sa prihlasenie hned prejavilo
if (isset($_POST['submit'])) {

    if (verify_user($mysqli, $email, $_POST['pswd'])) {
        // spravne udaje
        $user = select_user($mysqli, $_POST['email']);
        $_SESSION[SESSION_USER_ID] = $user['id'];
        $_SESSION[SESSION_USER_EMAIL] = $user['email'];
    }
    else {
        // nespravne udaje
        $password_incorrect = false;
    }
}
include('navbar.php');

if (!isset($_SESSION[SESSION_USER_ID])) { ?>
    <div class="row">
        <div class="col-md-4">
            <div class="container">
                <h2>Prihlásenie do parlamentu</h2>
                <?php if (isset($password_incorrect) && !$password_incorrect) echo '<p class="text-danger m-0">Nesprávne meno alebo heslo.</p>' ?>
                <form method="post">
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" placeholder="Zadajte email" name="email" value="test@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="pwd" class="form-label">Heslo:</label>
                        <input type="password" class="form-control" id="pwd" placeholder="Zadajte heslo" name="pswd" value="heslo123">
                    </div>
                    <div class="form-check mb-3">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="remember"> Zapamätať prihlásenie
                        </label>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Prihlásiť</button>
                </form>
            </div>
        </div>
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
    </div>
<?php }

include('footer.php');
