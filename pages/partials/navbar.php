<?php
// PHP membaca status dari Cookie agar checkbox tidak reset saat pindah halaman
$is_dark = (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') ? 'checked' : '';
?>
<nav>
    <i class='bx bx-menu'></i>
    <input type="checkbox" id="switch-mode" hidden <?= $is_dark; ?>>
    <label for="switch-mode" class="switch-mode"></label>
    
    <form action="data_arsip.php" method="GET">
        <div class="form-input">
            <input type="search" name="search" placeholder="Cari Arsip..." required>
            <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
        </div>
    </form>
    
    <div class="profile-details" style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 14px; font-weight: 600; color: var(--dark);"><?= $_SESSION['nama']; ?></span>
        <a href="profil.php" class="profile">
            <img src="<?= (!empty($_SESSION['foto'])) ? '../../assets/img/profiles/'.$_SESSION['foto'] : '../../assets/img/profiles/default.jpg' ?>">
        </a>
    </div>
</nav>