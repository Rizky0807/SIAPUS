<?php

$is_dark = (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') ? 'checked' : '';
?>
<!-- NAVBAR -->
<nav>
    <i class='bx bx-menu'></i>
    <input type="checkbox" id="switch-mode" hidden <?= $is_dark; ?>>
    <label for="switch-mode" class="switch-mode"></label>
    <form action="../user/arsip.php" method="GET">
        <div class="form-input">
            <input type="search" name="search" placeholder="Cari Arsip Digital..." required>
            <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
        </div>
    </form>
    <a href="#" class="profile">
        <img src="<?=  !empty($_SESSION['foto']) ? '../../assets/img/user'.$_SESSION['foto'] : '../../assets/img/default-user.png' ?>">
    </a>
</nav>
<!-- NAVBAR -->