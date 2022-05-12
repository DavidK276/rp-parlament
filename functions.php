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
 * retrieves all data from table 'poslancec' and 'osobne_udaje' for display to the user
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

/**
 * retrieves all data from table 'admin' and 'osobne_udaje' for display to the user
 * @param mysqli $mysqli
 * @return array[]
 */
function get_all_admini(mysqli $mysqli): array
{
    if (!$mysqli->connect_errno) {
        $sql = "SELECT admin.id, titul, osobne_udaje.meno, priezvisko FROM osobne_udaje, admin WHERE admin.id_udaje=osobne_udaje.id";
        return $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}


function exception_handler(Throwable $exception): void {
    display_error('Nastala neznáma chyba.');
}

if (!DEBUG) set_exception_handler('exception_handler');