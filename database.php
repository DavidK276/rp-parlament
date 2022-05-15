<?php
try {
    $mysqli = new mysqli('127.0.0.1', 'david', 'heslo', 'parlament');
}
catch (mysqli_sql_exception) {
    echo '<p class="text-danger">Nepodarilo sa nadviazať spojenie s databázou.</p>';
    exit();
} finally {
    $mysqli -> set_charset('utf8mb4');
    Admin::$mysqli = $mysqli;
    Poslanec::$mysqli = $mysqli;
    OsobneUdaje::$mysqli = $mysqli;
    BezpecnostnaPrevierka::$mysqli = $mysqli;
    PoslaneckyKlub::$mysqli = $mysqli;
}

