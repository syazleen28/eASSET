<!-- SIDEBAR -->
<div class="sidebar">

    <a href="dashboard.php">
        <i class="bi bi-grid-fill"></i> Dashboard
    </a>

    <!-- CONFIGURATION DROPDOWN -->
    <div class="sidebar-item">
        <a href="javascript:void(0)" class="dropdown-btn">
            <i class="bi bi-gear-fill"></i> Configuration
            <i class="bi bi-chevron-down float-end"></i>
        </a>

        <div class="dropdown-container">
            <a href="config_user.php">System User</a>
            <a href="config_category.php">Asset Category</a>
            <a href="config_supplier.php">Supplier</a>
        </div>
    </div>
        <a href="index_asset.php">
                <i class="bi bi-laptop-fill"></i> Assets
            </a>

    <a href="index_maintenance.php">
        <i class="bi bi-tools"></i> Maintenance
    </a>

    <a href="index_report.php">
        <i class="bi bi-bar-chart-fill"></i> Report
    </a>
</div>
</div>
<script>
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        this.classList.toggle("active");
        let dropdown = this.nextElementSibling;
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });
});
</script>

