<?php
session_start();
require_once 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// ================= AUTH =================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ================= GET FILTERS =================
$reportType = $_GET['report_type'] ?? '';
$fromDate   = $_GET['from_date'] ?? '';
$toDate     = $_GET['to_date'] ?? '';
$categoryIds = $_GET['category_id'] ?? [];
$statuses    = $_GET['asset_status'] ?? [];

if (!is_array($categoryIds) && $categoryIds !== '') $categoryIds = [$categoryIds];
if (!is_array($statuses) && $statuses !== '') $statuses = [$statuses];

// ================= FETCH DATA =================
$data = [];
if (!empty($reportType)) {

    if ($reportType === 'asset') {
        $sql = "SELECT a.asset_code, c.category_name, a.serial_number, a.asset_name, a.asset_status, 
                       a.purchase_date, a.assigned_user, a.location, a.spec, a.drive_info
                FROM assets a
                LEFT JOIN asset_categories c ON c.id = a.category_id
                WHERE 1=1";

        $params = [];

        if (!empty($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= " AND a.category_id IN ($placeholders)";
            $params = array_merge($params, $categoryIds);
        }

        if (!empty($statuses)) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND a.asset_status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        }

        if ($fromDate && $toDate) {
            $sql .= " AND a.purchase_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($reportType === 'maintenance') {
        $sql = "SELECT c.category_name, a.serial_number, a.asset_name, a.asset_status, m.issue_occurred
                FROM asset_maintenance m
                JOIN assets a ON a.id = m.asset_id
                JOIN asset_categories c ON c.id = a.category_id
                WHERE 1=1";

        $params = [];

        if (!empty($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= " AND a.category_id IN ($placeholders)";
            $params = array_merge($params, $categoryIds);
        }

        if ($fromDate && $toDate) {
            $sql .= " AND m.issue_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ================= CREATE SPREADSHEET =================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Determine total columns
$totalColumns = ($reportType === 'asset') ? 11 : 6; // asset now has 11 columns
$columns = range('A', 'Z');

// ----------------- COMPANY TITLE -----------------
$sheet->mergeCells('A1:' . $columns[$totalColumns - 1] . '1');
$sheet->setCellValue('A1', 'RN TECHNOLOGIES SDN. BHD.0');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// ----------------- REPORT TITLE -----------------
$sheet->mergeCells('A2:' . $columns[$totalColumns - 1] . '2');
$sheet->setCellValue('A2', strtoupper($reportType) . ' REPORT');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// ----------------- DATE RANGE -----------------
$dateText = ($fromDate && $toDate) ? "From: $fromDate To: $toDate" : "";
$sheet->mergeCells('A3:' . $columns[$totalColumns - 1] . '3');
$sheet->setCellValue('A3', $dateText);
$sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(12);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// ----------------- HEADER ROW -----------------
$startRow = 4;
$headers = ($reportType === 'asset')
    ? ['No','Asset Code','Category','Serial Number','Asset Name','Status','Purchase Date','Assigned User','Location','Spec','Drive Information']
    : ['No','Category','Serial Number','Asset Name','Status','Issue / Problem'];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col.$startRow, $header);
    $sheet->getStyle($col.$startRow)->getFont()->setBold(true);
    $sheet->getStyle($col.$startRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
    $sheet->getStyle($col.$startRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($col.$startRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $col++;
}

// ----------------- DATA ROWS -----------------
$rowNum = $startRow + 1;
$no = 1;
$statusColors = [
    'Available'   => 'FF28A745',
    'Maintenance' => 'FFFFC107',
    'Damaged'     => 'FFDC3545',
    'In Use'      => 'FF0D6EFD',
];

foreach ($data as $row) {
    $col = 'A';
    $sheet->setCellValue($col.$rowNum, $no++);
    $sheet->getStyle($col.$rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $col++;

    if ($reportType === 'asset') {
        $fields = ['asset_code','category_name','serial_number','asset_name','asset_status','purchase_date','assigned_user','location','spec','drive_info'];
    } else {
        $fields = ['category_name','serial_number','asset_name','asset_status','issue_occurred'];
    }

    foreach ($fields as $key) {
        $value = $row[$key] ?? '';
        if ($key === 'asset_status' && isset($statusColors[$value])) {
            $sheet->setCellValue($col.$rowNum, $value);
            $sheet->getStyle($col.$rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColors[$value]);
            $sheet->getStyle($col.$rowNum)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $sheet->getStyle($col.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            $sheet->setCellValue($col.$rowNum, $value);
        }
        $sheet->getStyle($col.$rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $col++;
    }
    $rowNum++;
}

// ----------------- AUTO SIZE / WRAP & ALIGN -----------------
foreach(range('A', $columns[$totalColumns - 1]) as $i => $columnID) {
    if ($reportType === 'asset' && in_array($columnID, ['J','K'])) { // Spec & Drive Info
        $sheet->getColumnDimension($columnID)->setWidth(30); // Fixed width
        $sheet->getStyle($columnID.'4:'.$columnID.$rowNum)
              ->getAlignment()
              ->setWrapText(true)
              ->setHorizontal(Alignment::HORIZONTAL_CENTER)
              ->setVertical(Alignment::VERTICAL_CENTER);
    } else {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        $sheet->getStyle($columnID.'4:'.$columnID.$rowNum)
              ->getAlignment()
              ->setHorizontal(Alignment::HORIZONTAL_CENTER)
              ->setVertical(Alignment::VERTICAL_CENTER);
    }
}


// ----------------- PRINT SETTINGS -----------------
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.75);
$sheet->getPageMargins()->setRight(0.5);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.75);

// ----------------- EXPORT -----------------
$filename = $reportType.'_report_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
