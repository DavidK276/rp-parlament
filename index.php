<?php
session_start();
include('constants.php');
include('functions.php');
head();
include('navbar.php'); ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3>O projekte</h3>
                <p>Webová aplikácia na tému "Poslanci v parlamente". Funkcie:</p>
                <ul class="list-group">
                    <li class="list-group-item">
                        <h5>Poslanci</h5>
                        <ul>
                            <li>Osobné údaje</li>
                            <li>Prihlásenie</li>
                            <li>Bezpečnostná previerka</li>
                            <li>Poslanecký klub</li>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <h5>Administrátori</h5>
                        <ul>
                            <li>Pridanie, úprava poslancov</li>
                            <li>Správa bezp. previerky</li>
                            <li>Správa posl. klubov</li>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <h5>Ostatní</h5>
                        <ul>
                            <li>Zobrazenie poslanov</li>
                            <li>Filtrovanie podľa kritérii</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

<?php include('footer.php');
