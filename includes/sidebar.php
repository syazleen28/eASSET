<?php
// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <a href="../dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-grid-fill"></i> Dashboard
    </a>

    <a href="../config_category.php" class="<?= in_array($currentPage, ['config_category.php', 'add_category.php', 'edit_category.php', 'view_category.php']) ? 'active' : '' ?>">
        <i class="bi bi-gear-fill"></i> Configuration
    </a>

    <a href="../asset_list.php" class="<?= $currentPage == 'asset_list.php' ? 'active' : '' ?>">
        <i class="bi bi-laptop-fill"></i> Assets
    </a>

    <a href="../allocation.php" class="<?= $currentPage == 'allocation.php' ? 'active' : '' ?>">
        <i class="bi bi-arrow-repeat"></i> Allocation
    </a>

    <a href="../maintenance.php" class="<?= $currentPage == 'maintenance.php' ? 'active' : '' ?>">
        <i class="bi bi-tools"></i> Maintenance
    </a>

    <a href="../report.php" class="<?= $currentPage == 'report.php' ? 'active' : '' ?>">
        <i class="bi bi-bar-chart-fill"></i> Report
    </a>
</div>
