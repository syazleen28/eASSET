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

.card-text-group {
    margin-left: 10px;     /* move text slightly to the right */
    text-align: left;     /* better alignment with icon */
}

.card-text-group div {
    font-size: 19px;      /* label size */
}

.card-text-group span {
    font-size: 40px;      /* number bigger */
    font-weight: 700;
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
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-text-group">
                <div>Available</div>
                <span><?= $available ?></span>
            </div>
            <img src="assets/images/available.png" alt="Available" style="width:50px; height:50px;">
        </div>
    </div>
</div>

        <div class="col-md-3">
    <div class="metrics-box text-primary">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-text-group">
                <div>In Use</div>
                <span><?= $inUse ?></span>
            </div>
            <img src="assets/images/use.png" style="width:50px;height:50px;">
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="metrics-box text-danger">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-text-group">
                <div>Damaged</div>
                <span><?= $broken ?></span>
            </div>
            <img src="assets/images/damaged.png" style="width:50px;height:50px;">
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="metrics-box">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-text-group" style="color:#ff9800;">
                <div>Maintenance</div>
                <span><?= $maintenance ?></span>
            </div>
            <img src="assets/images/maintenance.png" style="width:50px;height:50px;">
        </div>
    </div>
</div>


    <!-- CHART -->
    <div class="table-container">
     <h5 class="mb-3 fw-bold">Assets by Category & Status</h5>

        <canvas id="assetsChart" height="120"></canvas>
    </div>
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
