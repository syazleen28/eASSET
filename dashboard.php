<?php
session_start();
require_once 'config/database.php';

// Correct session variable check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Dummy metrics (replace with DB queries later)
$available = 30;
$inUse = 30;
$broken = 30;
$maintenance = 30;

$categories = ['PC', 'Laptop', 'Monitor', 'Keyboard'];
$availableData = [10, 15, 20, 25];
$inUseData = [5, 10, 15, 20];
$brokenData = [3, 5, 7, 10];
$maintenanceData = [2, 5, 10, 15];

$recentAllocations = [
    ['Laptop', 'John Doe', '2025-12-21', 'Active'],
    ['Monitor', 'Jane Smith', '2025-12-20', 'Returned']
];
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
.metrics-box { background: #fff; border-radius: 8px; padding: 15px; text-align: center; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.table-container { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); margin-bottom: 1rem; }
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h3 class="mb-3">DASHBOARD</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="metrics-box">Available<br><span class="fs-4"><?= $available ?></span></div></div>
        <div class="col-md-3"><div class="metrics-box">In Use<br><span class="fs-4"><?= $inUse ?></span></div></div>
        <div class="col-md-3"><div class="metrics-box">Damaged<br><span class="fs-4"><?= $broken ?></span></div></div>
        <div class="col-md-3"><div class="metrics-box">Maintenance<br><span class="fs-4"><?= $maintenance ?></span></div></div>
    </div>

    <div class="table-container mb-4">
        <h5>Assets by Category</h5>
        <canvas id="assetsChart"></canvas>
    </div>

    <div class="table-container">
        <h5>Recent Asset Allocations</h5>
        <table class="table table-bordered">
            <thead>
                <tr><th>Asset</th><th>Allocated To</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentAllocations as $row): ?>
                <tr>
                    <td><?= $row[0] ?></td>
                    <td><?= $row[1] ?></td>
                    <td><?= $row[2] ?></td>
                    <td><?= $row[3] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
            { label: 'Broken', data: <?= json_encode($brokenData) ?>, backgroundColor: '#f44336' },
            { label: 'Maintenance', data: <?= json_encode($maintenanceData) ?>, backgroundColor: '#ff9800' }
        ]
    },
    options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
});
</script>

</body>
</html>
