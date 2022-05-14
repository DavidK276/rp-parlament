<?php
include('classes.php');
session_start();
include('constants.php');
include('database.php');
include('functions.php');
head('Kluby');
include('navbar.php');

if (!isset($_SESSION[SESSION_USER_ROLE]) || $_SESSION[SESSION_USER_ROLE] != ROLE_ADMIN) {
    display_error('K tejto stránke nemáte prístup');
    include('footer.php');
    exit;
}
if (isset($_POST['submit'])) {
    $klub = new PoslaneckyKlub();
    $klub->nazov = $_POST['nazov'];
    try {
        $klub->insert();
    }
    catch (AttributeException) {
        http_response_code(400);
        display_error('Chybná požiadavka');
    }
}
?>
    <div class="container">
        <div class="row">
            <h2>Správa poslaneckých klubov</h2>
            <?php $kluby = get_all_kluby($GLOBALS['mysqli']);
            $kluby = partition($kluby, 3); ?>
            <div class="container">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <ul class="list-group">
                            <?php foreach ($kluby[0] as $klub) { ?>
                                <li class="list-group-item"><?= $klub['nazov'] ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <ul class="list-group">
                            <?php foreach ($kluby[1] as $klub) { ?>
                                <li class="list-group-item"><?= $klub['nazov'] ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <ul class="list-group">
                            <?php foreach ($kluby[2] as $klub) { ?>
                                <li class="list-group-item"><?= $klub['nazov'] ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <form method="post" class="needs-validation" novalidate>
                            <div class="input-group has-validation">
                                <!--                                <label for="nazov" class="form-label"><b class="text-danger">*</b>&nbsp;Pridať klub-->
                                <!--                                    <i class="material-icons"-->
                                <!--                                       title="Názov klubu musí byť kratší ako 50 znakov">help</i>-->
                                <!--                                </label>-->
                                <span class="input-group-text">Pridať klub&nbsp;
                                <i class="material-icons"
                                   title="Názov klubu musí byť kratší ako 50 znakov">help</i>
                                </span>
                                <input type="text" name="nazov" id="nazov" class="form-control"
                                       placeholder="Zadajte názov" aria-label="Pridať klub" required>
                                <button type="submit" name="submit" class="btn btn-primary">Potvrdiť</button>
                                <div class="invalid-feedback" id="nazov_feedback">Zadajte názov</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include('footer.php'); ?>

<script>
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        let forms = document.querySelectorAll('.needs-validation');

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    let name_input = document.getElementById('nazov');

                    if (name_input.value.length >= 50) {
                        name_input.setCustomValidity('Názov klubu musí byť kratší ako 50 znakov');
                        document.getElementById('nazov_feedback').innerHTML = 'Názov klubu musí byť kratší ako 50 znakov';
                    } else {
                        name_input.setCustomValidity('');
                        document.getElementById('nazov_feedback').innerHTML = 'Zadajte názov';
                    }
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
                form.addEventListener('input', function () {
                    form.classList.remove('was-validated');
                });
            });
        document.getElementById('form_filter').addEventListener('input', function () {
            document.getElementById('form_filter').submit();
        });
    })()
</script>
