<?php
date_default_timezone_set('Europe/Bratislava');

/**
 * Partitions array into a specified number of arrays
 * @param array $arr array to partition
 * @param int $p number of arrays to produce
 * @return array n+1 dismensional array
 */
function partition(array $arr, int $p): array
{
    $arrlen = count($arr);
    $partlen = floor($arrlen / $p);
    $partrem = $arrlen % $p;
    $partition = array();
    $mark = 0;
    for ($px = 0; $px < $p; $px++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($arr, $mark, $incr);
        $mark += $incr;
    }
    return $partition;
}

/**
 * Produces a html head with the specified title
 * @param string $title
 * @return void
 */
function head(string $title = 'Úvod'): void
{ ?>
    <!DOCTYPE html>
    <html lang="sk">
    <meta charset="UTF-8">
    <title><?= $title . " - Fiktívny Parlament"; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<body>
<header class="container-fluid bg-primary bg-gradient text-white">
    <h1>Fiktívny Parlament</h1>
    <h4 class="mb-0 pb-2"><?= $title; ?></h4>
</header>
<?php }

/**
 * Displays an error message with the specified test to the user
 * @param string $error_text
 * @return void
 */
function display_error(string $error_text): void
{
    echo "<div class=\"container\"><div class=\"row\"><div class=\"col-md-12\"><h3>$error_text</h3></div></div></div>";
}

///**
// * verifies the user login with the database
// * @param mysqli $mysqli
// * @param string $email
// * @param string $pswd
// * @return int -1 on error, role of the user on success
// */
//function verify_user(mysqli $mysqli, string $email, string $pswd): int
//{
//    if (!$mysqli->connect_errno) {
//        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
//        $stmt = $mysqli->prepare('SELECT heslo FROM osobne_udaje, poslanec WHERE poslanec.id_udaje=osobne_udaje.id AND osobne_udaje.email=?');
//        $stmt->bind_param('s', $email);
//        $stmt->execute();
//        $result = $stmt->get_result();
//        $stmt->close();
//        if ($result->num_rows > 0) {
//            $row = $result->fetch_assoc();
//            $result->free();
//            if (password_verify($pswd, $row['heslo'])) return ROLE_POSLANEC;
//            else return -1; // nespravne heslo
//        } else {
//            $stmt = $mysqli->prepare('SELECT heslo FROM osobne_udaje, admin WHERE admin.id_udaje=osobne_udaje.id AND osobne_udaje.email=?');
//            $stmt->bind_param('s', $email);
//            $stmt->execute();
//            $result = $stmt->get_result();
//            $stmt->close();
//            if ($result->num_rows > 0) {
//                $row = $result->fetch_assoc();
//                $result->free();
//                if (password_verify($pswd, $row['heslo'])) return ROLE_ADMIN;
//                else return -1; // nespravne heslo
//            }
//        }
//    }
//    return -1;
//}

/**
 * queries the database to find if the user exists
 * @param mysqli $mysqli
 * @param string $email an email of the user to query
 * @return bool
 */
function user_exists(mysqli $mysqli, string $email): bool
{
    if (!$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT id FROM osobne_udaje WHERE email=?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $result->free();
            return true;
        }
        $result->free();
    }
    return false;
}

function select_osobne_udaje(mysqli $mysqli, string $email): array
{
    if ((!empty($email) || !empty($user_id)) && !$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT * FROM osobne_udaje WHERE osobne_udaje.email=?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $result->free();
            return $row;
        } else return [];
    }
    return [];
}

function select_poslanec(mysqli $mysqli, string $email='', int $id=0): array
{
    if (!$mysqli->connect_errno) {
        if ($id != 0) {
            $stmt = $mysqli->prepare('SELECT poslanec.id, id_udaje, titul, osobne_udaje.email, meno, priezvisko, adresa, specializacia, osobne_udaje.id AS `table_udaje_id` FROM osobne_udaje, poslanec WHERE osobne_udaje.id=poslanec.id_udaje AND poslanec.id=?');
            return select_user($stmt, $id);
        }
        else {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $stmt = $mysqli->prepare('SELECT poslanec.id, id_udaje, titul, osobne_udaje.email, meno, priezvisko, adresa, specializacia, osobne_udaje.id AS `table_udaje_id` FROM osobne_udaje, poslanec WHERE osobne_udaje.id=poslanec.id_udaje AND osobne_udaje.email=?');
            return select_user($stmt, $email);
        }
    }
    return [];
}

function select_admin(mysqli $mysqli, string $email='', int $id=0): array
{
    if (!$mysqli->connect_errno) {
        if ($id != 0) {
            $stmt = $mysqli->prepare('SELECT osobne_udaje.id, email, meno, priezvisko, adresa, osobne_udaje.id AS `table_udaje_id` FROM osobne_udaje, admin WHERE osobne_udaje.id=admin.id_udaje AND admin.id=?');
            return select_user($stmt, $id);
        }
        else {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $stmt = $mysqli->prepare('SELECT osobne_udaje.id, email, meno, priezvisko, adresa, osobne_udaje.id AS `table_udaje_id` FROM osobne_udaje, admin WHERE osobne_udaje.id=admin.id_udaje AND osobne_udaje.email=?');
            return select_user($stmt, $email);
        }
    }
    return [];
}

function get_bezp_previerka(mysqli $mysqli, string $email='', int $id=0): array
{
    if (!$mysqli->connect_errno) {
        if ($id != 0) {
            $stmt = $mysqli->prepare('SELECT bp.uroven, datum, platnost, ou.id, meno, priezvisko FROM bezp_previerka bp INNER JOIN poslanec pos ON bp.id = pos.id_previerka INNER JOIN admin a ON bp.kto_udelil = a.id INNER JOIN osobne_udaje ou on a.id_udaje = ou.id WHERE pos.id=?');
            return select_user($stmt, $id);
        }
        else {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $stmt = $mysqli->prepare('SELECT bp.uroven, datum, platnost, ou.id, meno, priezvisko FROM bezp_previerka bp INNER JOIN poslanec pos ON bp.id = pos.id_previerka INNER JOIN admin a ON bp.kto_udelil = a.id INNER JOIN osobne_udaje ou on a.id_udaje = ou.id WHERE ou.email=?');
            return select_user($stmt, $email);
        }
    }
    return [];
}

function select_user(mysqli_stmt $stmt, string $id): array
{
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    } else return []; // pouzivatel sa nenasiel
}

function insert_osobne_udaje(mysqli $mysqli, string $email, string $meno, string $priezvisko, string $adresa): int
{
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare('INSERT INTO osobne_udaje(email, meno, priezvisko, adresa) VALUES(?, ?, ?, ?)');
        $stmt->bind_param('ssss', $email, $meno, $priezvisko, $adresa);
        $stmt->execute();
        if (!$stmt->errno) return $stmt->insert_id;
    }
    return -1;
}

function insert_poslanec(mysqli $mysqli, array $poslanec): int
{
    if (user_exists($mysqli, $poslanec['email'])) return ERROR_USER_EXISTS;
    if (!$mysqli->connect_errno) {
        $udaje = insert_osobne_udaje($mysqli, $poslanec['email'], $poslanec['meno'], $poslanec['priezvisko'], $poslanec['adresa']);
        if ($udaje > 0) {
            $stmt = $mysqli->prepare('INSERT INTO poslanec(id_udaje, specializacia, titul, heslo) VALUES(?, ?, ?, ?)');
            $pswd = password_hash($poslanec['heslo'], PASSWORD_DEFAULT);
            $stmt->bind_param('isss', $udaje, $poslanec['specializacia'], $poslanec['titul'], $pswd);
            $stmt->execute();
            if (!$stmt->errno) return SUCCESS;
        }
    }
    return ERROR_UNKNOWN;
}

function insert_admin(mysqli $mysqli, array $admin): bool
{
    if (user_exists($mysqli, $admin['email'])) return ERROR_USER_EXISTS;
    if (!$mysqli->connect_errno) {
        $udaje = insert_osobne_udaje($mysqli, $admin['email'], $admin['meno'], $admin['priezvisko'], $admin['adresa']);
        if ($udaje > 0) {
            $stmt = $mysqli->prepare('INSERT INTO admin(id_udaje, id_previerka, heslo) VALUES(?, ?, ?)');
            $pswd = password_hash($admin['heslo'], PASSWORD_DEFAULT);
            $stmt->bind_param('iis', $udaje, $admin['id_previerka'], $pswd);
            $stmt->execute();
            if (!$stmt->errno) return SUCCESS;
        }
    }
    return ERROR_UNKNOWN;
}

function update_osobne_udaje(mysqli $mysqli, string $email, string $meno, string $priezvisko, string $adresa): int
{
    // TODO: fixnut duplicitny email
    if (!$mysqli->connect_errno) {
        $old_udaje = select_osobne_udaje($mysqli, $email);
        if (user_exists($mysqli, $email) && $old_udaje['email'] != $email) return ERROR_USER_EXISTS;
        $stmt = $mysqli->prepare("UPDATE osobne_udaje SET email=?,meno=?,priezvisko=?,adresa=? WHERE osobne_udaje.id=?");
        $email = $email ?: $old_udaje['email'];
        $meno = $meno ?: $old_udaje['meno'];
        $priezvisko = $priezvisko ?: $old_udaje['priezvisko'];
        $adresa = $adresa ?: $old_udaje['adresa'];
        $id = $ou_id ?: $old_udaje['id'];
        $stmt->bind_param('ssssi', $email, $meno, $priezvisko, $adresa, $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return SUCCESS;
    }
    return ERROR_UNKNOWN;
}

function update_admin(mysqli $mysqli, array $admin): int
{
    if (!$mysqli->connect_errno) {
        $old_data = select_admin($mysqli, $admin['email']);
        $update_result = update_osobne_udaje($mysqli, $admin['email'], $admin['meno'], $admin['priezvisko'], $admin['adresa']);
        if ($update_result == ERROR_USER_EXISTS) return ERROR_USER_EXISTS;
        $stmt = $mysqli->prepare("UPDATE admin SET id_previerka=?,heslo=? WHERE id=?");
        $pswd = password_hash($admin['heslo'], PASSWORD_DEFAULT);
        $stmt->bind_param('isi', $admin['id_previerka'] ?: $old_data['id_previerka'], empty($admin['heslo']) ? $old_data['heslo'] : $pswd, $old_data['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return SUCCESS;
    }
    return ERROR_UNKNOWN;
}

function update_poslanec(mysqli $mysqli, array $poslanec): int
{
    if (!$mysqli->connect_errno) {
        $old_data = select_poslanec($mysqli, id: $poslanec['id']);
        $update_result = update_osobne_udaje($mysqli, $poslanec['email'], $poslanec['meno'], $poslanec['priezvisko'], $poslanec['adresa'], $old_data['id_udaje']);
        if ($update_result == ERROR_USER_EXISTS) return ERROR_USER_EXISTS;
        if (isset($poslanec['titul']) && !empty($poslanec['titul'])) update_poslanec_titul($mysqli, $poslanec['titul'], $old_data['id']);
        if (isset($poslanec['heslo']) && !empty($poslanec['heslo'])) update_poslanec_heslo($mysqli, $poslanec['heslo'], $old_data['id']);
        if (isset($poslanec['id_klub']) && !empty($poslanec['id_klub'])) update_poslanec_klub($mysqli, $poslanec['id_klub'], $old_data['id']);
        if (isset($poslanec['id_previerka']) && !empty($poslanec['id_previerka'])) update_poslanec_previerka($mysqli, $poslanec['id_previerka'], $old_data['id']);
        if (isset($poslanec['specializacia']) && !empty($poslanec['specializacia'])) update_poslanec_specializacia($mysqli, $poslanec['specializacia'], $old_data['id']);
        return SUCCESS;
    }
    return ERROR_UNKNOWN;
}

function update_poslanec_previerka(mysqli $mysqli, int $previerka, int $id): void
{
    $stmt = $mysqli->prepare("UPDATE poslanec SET id_previerka=? WHERE poslanec.id=?");
    $stmt->bind_param('ii', $previerka, $id);
    $stmt->execute();
}

function update_poslanec_klub(mysqli $mysqli, int $klub, int $id): void
{
    $stmt = $mysqli->prepare("UPDATE poslanec SET id_klub=? WHERE poslanec.id=?");
    $stmt->bind_param('ii', $klub, $id);
    $stmt->execute();
}

function update_poslanec_heslo(mysqli $mysqli, string $heslo, int $id): void
{
    $stmt = $mysqli->prepare("UPDATE poslanec SET heslo=? WHERE poslanec.id=?");
    $heslo = password_hash($heslo, PASSWORD_DEFAULT);
    $stmt->bind_param('si', $heslo, $id);
    $stmt->execute();
}

function update_poslanec_titul(mysqli $mysqli, string $titul, int $id): void
{
    $stmt = $mysqli->prepare("UPDATE poslanec SET titul=? WHERE poslanec.id=?");
    $stmt->bind_param('si', $titul, $id);
    $stmt->execute();
}

function update_poslanec_specializacia(mysqli $mysqli, string $specializacia, int $id): void
{
    $stmt = $mysqli->prepare("UPDATE poslanec SET specializacia=? WHERE poslanec.id=?");
    $stmt->bind_param('si', $specializacia, $id);
    $stmt->execute();
}

function delete_admin(mysqli $mysqli, string $email): int
{
    if (!$mysqli->connect_errno) {
        $admin = select_admin($mysqli, $email);
        $stmt = $mysqli->prepare('DELETE FROM admin WHERE id=?');
        $stmt->bind_param('i', $admin['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $mysqli->prepare('DELETE FROM osobne_udaje WHERE email=?');
            $stmt->bind_param('s', $admin['email']);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return SUCCESS;
        }
        return ERROR_USER_NOT_FOUND;
    }
    return ERROR_UNKNOWN;
}

function delete_poslanec(mysqli $mysqli, string $email): int
{
    if (!$mysqli->connect_errno) {
        $poslanec = select_poslanec($mysqli, $email);
        $stmt = $mysqli->prepare('DELETE FROM poslanec WHERE id=?');
        $stmt->bind_param('i', $poslanec['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $stmt = $mysqli->prepare('DELETE FROM osobne_udaje WHERE email=?');
            $stmt->bind_param('s', $poslanec['email']);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return SUCCESS;
        }
        return ERROR_USER_NOT_FOUND;
    }
    return ERROR_UNKNOWN;
}

/**
 * sanitises the user input
 * @param string $input
 * @return string
 */
function sanitise(string $input): string
{
    return trim(strip_tags($input));
}

/* kontroluje meno (meno a priezvisko)
vráti TRUE, ak celé meno ($input) obsahuje práve 1 medzeru, pred a za medzerou sú časti aspoň dĺžky 3 znaky
*/
/**
 * checks the validity of the name input by the user
 * @param string $input
 * @return bool true if input contains exactly one space and at least three characters before and after the space
 */
function verify_name(string $input): bool
{
    $space = strpos($input, ' ');
    if (!$space) return false;
    $last_name = substr($input, $space + 1);
    return ($space > 2 && (!str_contains($last_name, ' ')) && strlen($last_name) > 2);
}

/**
 * retrieves all possible values of 'specializacia' from the database
 * @param mysqli $mysqli
 * @return string[]
 */
function get_spec_values(mysqli $mysqli): array
{
    $type = $mysqli->query("SHOW COLUMNS FROM poslanec WHERE Field = 'specializacia'")->fetch_assoc()['Type'];
    preg_match("/^set\('(.*)'\)$/", $type, $matches);
    return explode("','", $matches[1]);
}

/**
 * retrieves all data from table 'poslanci' and 'osobne_udaje' for display to the user
 * @param mysqli $mysqli
 * @return array[]
 */
function get_all_poslanci(mysqli $mysqli): array
{
    if (!$mysqli->connect_errno) {
        $sql = "SELECT poslanec.id, titul, osobne_udaje.meno, priezvisko FROM osobne_udaje, poslanec WHERE poslanec.id_udaje=osobne_udaje.id";
        return $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function exception_handler(Throwable $exception): void {
    if (DEBUG) {
        echo "Uncaught exception: " , $exception->getMessage(), "\n";
        exit();
    }
    else {
        display_error('Nastala neznáma chyba.');
    }
}

set_exception_handler('exception_handler');