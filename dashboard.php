<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ===============================
   METRICS (TOP CARDS)
================================ */

// Available
$available = $pdo->query("SELECT COUNT(*) FROM assets WHERE asset_status = 'Available'")->fetchColumn();

// In Use
$inUse = $pdo->query("SELECT COUNT(*) FROM assets WHERE asset_status = 'In Use'")->fetchColumn();

// Damaged
$broken = $pdo->query("SELECT COUNT(*) FROM assets WHERE asset_status = 'Damaged'")->fetchColumn();

// Maintenance
$maintenance = $pdo->query("SELECT COUNT(*) FROM assets WHERE asset_status = 'Maintenance'")->fetchColumn();

/* ===============================
   CHART DATA (ASSETS BY CATEGORY)
================================ */

$sql = "
SELECT 
    c.category_name,
    SUM(CASE WHEN a.asset_status = 'Available' THEN 1 ELSE 0 END) AS available,
    SUM(CASE WHEN a.asset_status = 'In Use' THEN 1 ELSE 0 END) AS in_use,
    SUM(CASE WHEN a.asset_status = 'Damaged' THEN 1 ELSE 0 END) AS damaged,
    SUM(CASE WHEN a.asset_status = 'Maintenance' THEN 1 ELSE 0 END) AS maintenance
FROM asset_categories c
LEFT JOIN assets a ON a.category_id = c.id
GROUP BY c.id
ORDER BY c.category_name
";

$stmt = $pdo->query($sql);

$categories = [];
$availableData = [];
$inUseData = [];
$brokenData = [];
$maintenanceData = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row['category_name'];
    $availableData[] = (int)$row['available'];
    $inUseData[] = (int)$row['in_use'];
    $brokenData[] = (int)$row['damaged'];
    $maintenanceData[] = (int)$row['maintenance'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | eAssets</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="assets/images/style.css" rel="stylesheet">

<style>
.main-content { margin-left: 240px; padding: 90px 20px 20px; }
.metrics-box {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
}
.metrics-box span {
    font-size: 28px;
}
.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
}
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h3 class="mb-4">DASHBOARD</h3>

    <!-- METRICS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metrics-box text-success">
                Available
                <span><?= $available ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metrics-box text-primary">
                In Use
                <span><?= $inUse ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metrics-box text-danger">
                Damaged
                <span><?= $broken ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metrics-box text-warning">
                Maintenance
                <span><?= $maintenance ?></span>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="table-container">
        <h5 class="mb-3">Assets by Category & Status</h5>
        <canvas id="assetsChart" height="120"></canvas>
    </div>
</div>

<script>
new Chart(document.getElementById('assetsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($categories) ?>,
        datasets: [
            { label: 'Available', data: <?= json_encode($availableData) ?>, backgroundColor: '#4caf50' },
            { label: 'In Use', data: <?= json_encode($inUseData) ?>, backgroundColor: '#2196f3' },
            { label: 'Damaged', data: <?= json_encode($brokenData) ?>, backgroundColor: '#f44336' },
            { label: 'Maintenance', data: <?= json_encode($maintenanceData) ?>, backgroundColor: '#ff9800' }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
        }
    }
});

</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
