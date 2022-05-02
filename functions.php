<?php
date_default_timezone_set('Europe/Bratislava');

function head($title = 'Úvod'): void
{ ?>
    <!DOCTYPE html>
    <html lang="sk">
    <meta charset="UTF-8">
    <title><?php echo $title . " - Fiktívny Parlament"; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<body>
<header class="container-fluid bg-primary bg-gradient text-white">
    <h1>Fiktívny Parlament</h1>
    <h4 class="mb-0 pb-2"><?php echo $title; ?></h4>
</header>
<?php }

function verify_user($mysqli, $email, $pswd): int
{
    if (!$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT heslo FROM osobne_udaje, poslanec WHERE poslanec.id_udaje=osobne_udaje.id AND osobne_udaje.email=?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $result->free();
            if (password_verify($pswd, $row['heslo'])) return 1;
            else return -1; // nespravne heslo
        } else {
            $stmt = $mysqli->prepare('SELECT heslo FROM osobne_udaje, admin WHERE admin.id_udaje=osobne_udaje.id AND osobne_udaje.email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                if (password_verify($pswd, $row['heslo'])) return 2;
                else return -1; // nespravne heslo
            }
        }
    }
    return -1;
}

function select_osobne_udaje($mysqli, $email): array
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

function select_poslanec($mysqli, $email): array
{
    if ((!empty($email) || !empty($user_id)) && !$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT poslanec.*, osobne_udaje.email, meno, priezvisko, adresa, osobne_udaje.id AS `table_udaje_id` FROM osobne_udaje, poslanec WHERE osobne_udaje.id=poslanec.id_udaje AND osobne_udaje.email=?');
        return select_user($stmt, $email);
    }
    return [];
}

function select_admin($mysqli, $email): array
{
    if ((!empty($email) || !empty($user_id)) && !$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT admin.*, osobne_udaje.email, meno, priezvisko, adresa, osobne_udaje.id AS `table_udaje_id` FROM osobne_udaje, admin WHERE osobne_udaje.id=admin.id_udaje AND osobne_udaje.email=?');
        return select_user($stmt, $email);
    }
    return [];
}

function select_user($stmt, $email): array
{
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    } else return []; // pouzivatel sa nenasiel
}

function insert_osobne_udaje($mysqli, $email, $meno, $priezvisko, $adresa): int {
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare('INSERT INTO osobne_udaje(email, meno, priezvisko, adresa) VALUES(?, ?, ?, ?)');
        $stmt->bind_param('ssss', $email, $meno, $priezvisko, $adresa);
        $stmt->execute();
        if (!$stmt->errno) return $stmt->insert_id;
    }
    return -1;
}
// TODO: osetrit ak email existuje
function insert_poslanec($mysqli, $poslanec): bool
{
    if (!$mysqli->connect_errno) {
        $udaje = insert_osobne_udaje($mysqli, $poslanec['email'], $poslanec['meno'], $poslanec['priezvisko'], $poslanec['adresa']);
        if ($udaje > 0) {
            $stmt = $mysqli->prepare('INSERT INTO poslanec(id_udaje, specializacia, titul, heslo) VALUES(?, ?, ?, ?)');
            $stmt->bind_param('isss', $udaje, $poslanec['specializacia'],
                $poslanec['titul'], password_hash($poslanec['heslo'], PASSWORD_DEFAULT));
            $stmt->execute();
            if (!$stmt->errno) return true;
        }
    }
    return false;
}

function insert_admin($mysqli, $admin): bool
{
    if (!$mysqli->connect_errno) {
        $udaje = insert_osobne_udaje($mysqli, $admin['email'], $admin['meno'], $admin['priezvisko'], $admin['adresa']);
        if ($udaje > 0) {
            $stmt = $mysqli->prepare('INSERT INTO admin(id_udaje, id_previerka, heslo) VALUES(?, ?, ?)');
            $stmt->bind_param('iis', $udaje, $admin['id_previerka'], password_hash($admin['heslo'], PASSWORD_DEFAULT));
            $stmt->execute();
            if (!$stmt->errno) return true;
        }
    }
    return false;
}

function update_osobne_udaje($mysqli, $email, $meno, $priezvisko, $adresa) {
    // TODO: skontrolovat spravanie pri update emailu na duplicitny
    if (!$mysqli->connect_errno) {
        $old_udaje = select_osobne_udaje($mysqli, $email);
        $stmt = $mysqli->prepare("UPDATE osobne_udaje SET email=?,meno=?,priezvisko=?,adresa=? WHERE id=?");
        $stmt->bind_param('ssssi', empty($email) ? $old_udaje['email'] : $email,
            empty($admin['first_name']) ? $old_udaje['meno'] : $meno,
            empty($admin['last_name']) ? $old_udaje['priezvisko'] : $priezvisko,
            empty($admin['address']) ? $old_udaje['adresa'] : $adresa,
            $old_udaje['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return true;
    }
    return false;
}

function update_admin($mysqli, $admin): bool
{
    if (!$mysqli->connect_errno) {
        $old_data = select_admin($mysqli, $admin['email']);
        update_osobne_udaje($mysqli, $admin['email'], $admin['first_name'], $admin['last_name'], $admin['address']);
        $stmt = $mysqli->prepare("UPDATE admin SET id_previerka=?,heslo=? WHERE id=?");
        $stmt->bind_param('isi', empty($admin['id_previerka']) ? $old_data['id_previerka'] : $admin['id_previerka'],
            empty($admin['heslo']) ? $old_data['heslo'] : password_hash($admin['heslo'], PASSWORD_DEFAULT),
            $old_data['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return true;
    }
    return false;
}

function update_poslanec($mysqli, $poslanec): bool
{
    if (!$mysqli->connect_errno) {
        $old_data = select_poslanec($mysqli, $poslanec['email']);
        update_osobne_udaje($mysqli, $poslanec['email'], $poslanec['first_name'], $poslanec['last_name'], $poslanec['address']);
        $stmt = $mysqli->prepare("UPDATE poslanec SET id_klub=?,id_previerka=?,specializacia=?,titul=?,heslo=? WHERE poslanec.id=?");
        $stmt->bind_param('iisssi', empty($poslanec['id_klub']) ? $old_data['id_klub'] : $poslanec['id_klub'],
            empty($poslanec['id_previerka']) ? $old_data['id_previerka'] : $poslanec['id_previerka'],
            empty($poslanec['specializacia']) ? $old_data['specializacia'] : $poslanec['specializacia'],
            empty($poslanec['titul']) ? $old_data['titul'] : $poslanec['titul'],
            empty($poslanec['heslo']) ? $old_data['heslo'] : password_hash($poslanec['heslo'], PASSWORD_DEFAULT),
            $old_data['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return true;
    }
    return false;
}

function delete_admin($mysqli, $email): bool
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
            if ($stmt->affected_rows > 0) return true;
        }
    }
    return false;
}

function delete_poslanec($mysqli, $email): bool
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
            if ($stmt->affected_rows > 0) return true;
        }
    }
    return false;
}

function sanitise($input): string
{
    return addslashes(trim(strip_tags($input)));
}

/* kontroluje meno (meno a priezvisko)
vráti TRUE, ak celé meno ($input) obsahuje práve 1 medzeru, pred a za medzerou sú časti aspoň dĺžky 3 znaky
*/
function verify_name($input): bool
{
    $space = strpos($input, ' ');
    if (!$space) return false;
    $last_name = substr($input, $space + 1);
    return ($space > 2 && (!str_contains($last_name, ' ')) && strlen($last_name) > 2);
}

function get_spec_values($mysqli): array
{
    $type = $mysqli->query("SHOW COLUMNS FROM poslanec WHERE Field = 'specializacia'")->fetch_assoc()['Type'];
    preg_match("/^set\('(.*)'\)$/", $type, $matches);
    return explode("','", $matches[1]);
}