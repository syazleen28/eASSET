<?php
// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get staff name from session
$staffName = '';
if (isset($_SESSION['name'])) {
    $staffName = $_SESSION['name'];
}
?>

<!-- HEADER (NAVBAR) -->
<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">
        <img src="assets/images/logo_RNTECH.png" alt="Logo" height="30">
        <span class="brand-text">eASSETS</span>
    </a>

    <div class="ms-auto d-flex align-items-center">
        <span>
            Welcome<?php echo $staffName ? ', ' . htmlspecialchars($staffName) : ''; ?>!
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">
            Logout
        </a>
    </div>
</nav>

<!-- Optional CSS links -->
<link rel="stylesheet" href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css">
<link rel="stylesheet" href="assets/css/sidebar.css">
