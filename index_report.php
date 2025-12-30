<?php
session_start();
require_once 'config/database.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports | eAssets</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/images/style.css" rel="stylesheet">

<style>
.export-btns button {
    margin-left: 5px;
}
</style>
</head>

<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<h5>ASSET MANAGEMENT &gt; Reports</h5>

<!-- ================= FILTER SECTION ================= -->
<div class="card mt-3 mb-3">
    <div class="card-header fw-bold">
        Report Filter
    </div>

    <div class="card-body">
        <form method="get">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-select">
                        <option value="">-- Select Report --</option>
                        <option>Asset Report</option>
                        <option>Maintenance Report</option>
                        <option>Assignment Report</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select">
                        <option>All Categories</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Asset Status</label>
                    <select class="form-select">
                        <option>All</option>
                        <option>Available</option>
                        <option>Maintenance</option>
                        <option>Disposed</option>
                    </select>
                </div>

                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-primary mt-3">
                        <i class="bi bi-search"></i> Generate Report
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- ================= REPORT RESULT ================= -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Report Result</span>

        <!-- Export Buttons (UI only) -->
        <div class="export-btns">
            <button class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
            <button class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-word"></i> Word
            </button>
            <button class="btn btn-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Dummy Data -->
                    <tr>
                        <td>1</td>
                        <td>AST-0001</td>
                        <td>Dell Latitude 5420</td>
                        <td>Laptop</td>
                        <td>
                            <span class="badge bg-warning text-dark">Maintenance</span>
                        </td>
                        <td>15/01/2025</td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>AST-0002</td>
                        <td>HP LaserJet</td>
                        <td>Printer</td>
                        <td>
                            <span class="badge bg-success">Available</span>
                        </td>
                        <td>20/01/2025</td>
                    </tr>

                    <!-- Empty state -->
                    <!--
                    <tr>
                        <td colspan="6" class="text-center">
                            Please select filter and click Generate Report
                        </td>
                    </tr>
                    -->
                </tbody>
            </table>
        </div>

    </div>
</div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
