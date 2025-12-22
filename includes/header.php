<!-- HEADER (NAVBAR) -->
<nav class="navbar navbar-expand-lg fixed-top px-5 py-3"
     style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            height: 60px;">

    <a class="navbar-brand d-flex align-items-center text-white" href="dashboard.php" style="font-size: 2rem; margin-left: -15px;">
        <img src="assets/images/logo_RNTECH.png" alt="Logo" style="height: 55px; margin-right: 10px;">
        <span class="brand-text">eASSETS</span>
    </a>
    <div class="ms-auto d-flex align-items-center">
        <span class="text-white me-4" style="font-size: 1.2rem;">
            Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm" style="padding: 0.5rem 1rem; font-size: 1rem; border-radius: 12px;">
            Logout
        </a>
    </div>
</nav>
