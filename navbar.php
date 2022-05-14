<?php $page = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1); ?>
<nav class="navbar navbar-expand-md navbar-dark bg-secondary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Navigácia</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php if ($page == "index.php") echo 'active' ?>"
                       aria-current="page" href="index.php">Index</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($page == "poslanci.php") echo 'active' ?>" href="poslanci.php">Poslanci</a>
                </li>
                <?php
                if (isset($_SESSION[SESSION_USER])) { ?>
                    <?php if ($_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) { ?>
                        <li class="nav-item">
                            <a class="nav-link <?php if ($page == "admins.php") echo 'active' ?>" href="admins.php"
                               tabindex="-1"
                               aria-disabled="true">Admini</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if ($page == "klub.php") echo 'active' ?>" href="klub.php">Kluby</a>
                        </li>
                    <?php } ?>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link <?php if ($page == "konto.php") echo 'active' ?>" href="konto.php" tabindex="-1"
                       aria-disabled="true"><?php echo isset($_SESSION[SESSION_USER]) ? 'Konto' : 'Prihlásenie' ?></a>
                </li>
                <?php
                if (isset($_SESSION[SESSION_USER])) { ?>
                    <?php if ($_SESSION[SESSION_USER_ROLE] == ROLE_ADMIN) { ?>
                        <li class="nav-item">
                            <a class="nav-link <?php if ($page == "newuser.php") echo 'active' ?>" href="newuser.php"
                               tabindex="-1"
                               aria-disabled="true">Pridať</a>
                        </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Odhlásenie</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>