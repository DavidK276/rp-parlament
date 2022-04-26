<?php
date_default_timezone_set('Europe/Bratislava');

function head($title = 'Úvod')
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

function verify_user($mysqli, $email, $pswd)
{
    if (!$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT heslo FROM poslanec WHERE email=?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $result->free();
            if (password_verify($pswd, $row['heslo'])) return true;
            else return -1; // nespravne heslo
        } else {
            $stmt = $mysqli->prepare('SELECT heslo FROM admin WHERE email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                if (password_verify($pswd, $row['heslo'])) return true;
                else return -1; // nespravne heslo
            }
        }
    }
    return -1;
}

function select_poslanec($mysqli, $email = '', $user_id = '')
{
    if ((!empty($email) || !empty($user_id)) && !$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT * FROM poslanec WHERE email=? OR id=?');
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $result->free();
            return $row;
        } else return false; // pouzivatel sa nenasiel
    }
    return false;
}

function select_admin($mysqli, $email = '', $user_id = '')
{
    if ((!empty($email) || !empty($user_id)) && !$mysqli->connect_errno) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt = $mysqli->prepare('SELECT * FROM admin WHERE email=? OR id=?');
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $result->free();
            return $row;
        } else return false; // pouzivatel sa nenasiel
    }
    return false;
}

function insert_poslanec($mysqli, $poslanec)
{
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare('INSERT INTO poslanec(id_klub, id_previerka, specializacia, email, titul, meno, priezvisko, adresa, heslo) 
                                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iisssssss', $poslanec['id_klub'], $poslanec['id_previerka'], $poslanec['specializacia'],
            $poslanec['email'], $poslanec['titul'], $poslanec['meno'], $poslanec['prezvisko'],
            $poslanec['adresa'], password_hash($poslanec['heslo'], PASSWORD_DEFAULT));
        $stmt->execute();
        if (!$stmt->errno) return true;
    }
    return false;
}

function insert_admin($mysqli, $admin)
{
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare('INSERT INTO admin(id_previerka, email, meno, priezvisko, adresa, heslo)
                                    VALUES(?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssss', $admin['id_previerka'], $admin['email'], $admin['meno'], $admin['priezvisko'],
            $admin['adresa'], password_hash($admin['heslo'], PASSWORD_DEFAULT));
        $stmt->execute();
        if (!$stmt->errno) return true;
    }
    return false;
}

function update_admin($mysqli, $admin)
{
    if (!$mysqli->connect_errno) {
        $old_data = select_admin($mysqli, $admin['email']);
        $stmt = $mysqli->prepare("UPDATE admin SET email=?,id_previerka=?,meno=?,priezvisko=?,adresa=?,heslo=? WHERE id=?");
        $stmt->bind_param('sissssi', empty($email) ? $old_data['email'] : $admin['email'],
            empty($admin['id_previerka']) ? $old_data['id_previerka'] : $admin['id_previerka'],
            empty($admin['first_name']) ? $old_data['meno'] : $admin['first_name'],
            empty($admin['last_name']) ? $old_data['priezvisko'] : $admin['last_name'],
            empty($admin['address']) ? $old_data['adresa'] : $admin['address'],
            empty($admin['heslo']) ? $old_data['heslo'] : password_hash($admin['heslo'], PASSWORD_DEFAULT),
            $old_data['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return true;
    }
    return false;
}

function update_poslanec($mysqli, $poslanec)
{
    if (!$mysqli->connect_errno) {
        $old_data = select_poslanec($mysqli, $poslanec['email']);
        $stmt = $mysqli->prepare("UPDATE poslanec SET id_klub=?,id_previerka=?,specializacia=?,email=?,titul=?,meno=?,
                    priezvisko=?,adresa=?,heslo=? WHERE poslanec.id=?");
        $stmt->bind_param('iisssssssi', empty($poslanec['id_klub']) ? $old_data['id_klub'] : $poslanec['id_klub'],
            empty($poslanec['id_previerka']) ? $old_data['id_previerka'] : $poslanec['id_previerka'],
            empty($poslanec['specializacia']) ? $old_data['specializacia'] : $poslanec['specializacia'],
            empty($poslanec['email']) ? $old_data['email'] : $poslanec['email'],
            empty($poslanec['titul']) ? $old_data['titul'] : $poslanec['titul'],
            empty($poslanec['meno']) ? $old_data['meno'] : $poslanec['meno'],
            empty($poslanec['priezvisko']) ? $old_data['priezvisko'] : $poslanec['priezvisko'],
            empty($poslanec['adresa']) ? $old_data['adresa'] : $poslanec['adresa'],
            empty($poslanec['heslo']) ? $old_data['heslo'] : password_hash($poslanec['heslo'], PASSWORD_DEFAULT),
            $old_data['id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) return true;
    }
    return false;
}

function delete_admin($mysqli, $email = '', $user_id = '')
{
    if (!$mysqli->connect_errno) {
        if (!empty($user_id)) {
            $stmt = $mysqli->prepare('DELETE FROM admin WHERE id=?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return true;
        }
        else if (!empty($email)) {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $stmt = $mysqli->prepare('DELETE FROM admin WHERE email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return true;
        }
    }
    return false;
}

function delete_poslanec($mysqli, $email = '', $user_id = '')
{
    if (!$mysqli->connect_errno) {
        if (!empty($user_id)) {
            $stmt = $mysqli->prepare('DELETE FROM poslanec WHERE id=?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return true;
        }
        else if (!empty($email)) {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $stmt = $mysqli->prepare('DELETE FROM poslanec WHERE email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->affected_rows > 0) return true;
        }
    }
    return false;
}

function sanitise($input)
{
    return addslashes(trim(strip_tags($input)));
}

/* kontroluje meno (meno a priezvisko)
vráti TRUE, ak celé meno ($input) obsahuje práve 1 medzeru, pred a za medzerou sú časti aspoň dĺžky 3 znaky
*/
function verify_name($input)
{
    $space = strpos($input, ' ');
    if (!$space) return false;
    $last_name = substr($input, $space + 1);
    return ($space > 2 && (!str_contains($last_name, ' ')) && strlen($last_name) > 2);
}

function get_role_values($mysqli)
{
    $type = $mysqli->query("SHOW COLUMNS FROM pouzivatel WHERE Field = 'rola'")->fetch_assoc()['Type'];
    preg_match("/^enum\('(.*)'\)$/", $type, $matches);
    $enum = explode("','", $matches[1]);
    return $enum;
}