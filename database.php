<?php
$mysqli = new mysqli('127.0.0.1', 'david', 'heslo', 'parlament');
if ($mysqli -> connect_errno) {
    echo '<p class="text-danger">Nepodarilo sa nadviazať spojenie s databázou.</p>';
    exit();
}
else {
    $mysqli -> set_charset('utf8mb4');
    Admin::$mysqli = $mysqli;
    Poslanec::$mysqli = $mysqli;
    OsobneUdaje::$mysqli = $mysqli;
    BezpecnostnaPrevierka::$mysqli = $mysqli;
}

