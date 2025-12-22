<!-- HEADER (NAVBAR) -->
<nav class="navbar navbar-expand-lg fixed-top">
    <a class="navbar-brand" href="dashboard.php">
        <img src="assets/images/logo_RNTECH.png" alt="Logo">
        <span class="brand-text">eASSETS</span>
    </a>
    <div class="ms-auto d-flex align-items-center">
        <span>
            Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">
            Logout
        </a>
    </div>
</nav>
