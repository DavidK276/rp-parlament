<?php
session_start();
include('constants.php');
include('functions.php');
head('Poslanci');
include('navbar.php');
include('database.php');
$poslanci = get_all_poslanci($mysqli);
$poslanci = partition($poslanci, 3);
?>
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <ul class="list-group">
                <?php foreach ($poslanci[0] as $posl) {?>
                    <li class="list-group-item"><?php echo $posl['titul'] . ' ' . $posl['meno'] . ' ' . $posl['priezvisko']?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="list-group">
                <?php foreach ($poslanci[1] as $posl) {?>
                    <li class="list-group-item"><?php echo $posl['titul'] . ' ' . $posl['meno'] . ' ' . $posl['priezvisko']?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="col-md-4">
            <ul class="list-group">
                <?php foreach ($poslanci[2] as $posl) {?>
                    <li class="list-group-item"><?php echo $posl['titul'] . ' ' . $posl['meno'] . ' ' . $posl['priezvisko']?></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<?php include('footer.php');
