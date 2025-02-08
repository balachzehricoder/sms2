<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Database connection
require_once 'confiq.php';

// Get POST data for session and month
$session = isset($_POST['session']) ? trim($_POST['session']) : '';
$month = isset($_POST['month']) ? trim($_POST['month']) : '';

// Initialize Summary Data
$summary = [
    'previous_balance' => 0,
    'monthly_fee' => 0,
    'total_discount' => 0,
    'total_amount' => 0,
    'total_received' => 0,
    'total_receivables' => 0
];

// Fetch Fee Summary
$query = "SELECT 
            SUM(CASE WHEN status != 'paid' THEN arrears ELSE 0 END) AS previous_balance,
            SUM(total_amount) AS monthly_fee,
            SUM(discount) AS total_discount,
            SUM(final_amount) AS total_amount,
            SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) AS total_received,
            (SUM(final_amount) - SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END)) AS total_receivables
          FROM challans
          WHERE BINARY TRIM(challan_month) = BINARY TRIM('$month') 
          AND BINARY TRIM(challan_session) = BINARY TRIM('$session')";

$result = $conn->query($query);
if ($result) {
    $data = $result->fetch_assoc();
    if ($data) {
        $summary = $data;
    }
}

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Define Headers
$headers = ['S.No', 'Particulars', 'Amount'];
$totalColumns = count($headers);
$lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

// --- Setup Page for A4 Print ---
$sheet->getPageSetup()
    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)  // Keep portrait mode
    ->setPaperSize(PageSetup::PAPERSIZE_A4)            // A4 paper
    ->setFitToWidth(1)                                 // Fit to width
    ->setFitToHeight(0);                               // Adjust height dynamically

// --- School Name Header ---
$sheet->setCellValue('A1', 'Hazara Public School & College Jamber');
$sheet->mergeCells("A1:{$lastColumn}1");
$sheet->getStyle("A1")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 20, 'color' => ['argb' => 'FF000080']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6E6FA']]
]);

// --- Report Title ---
$sheet->setCellValue('A2', "MONTHLY FEE SUMMARY REPORT - $month $session");
$sheet->mergeCells("A2:{$lastColumn}2");
$sheet->getStyle("A2")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFB0C4DE']]
]);

// --- Table Headers ---
$sheet->fromArray($headers, null, 'A4');
$sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]
]);

// --- Table Data ---
$dataRows = [
    [1, "Previous Balance", number_format($summary['previous_balance'], 2)],
    [2, "Monthly Fee", number_format($summary['monthly_fee'], 2)],
    [3, "Miscellaneous", "0"],
    [4, "Transport", "0"],
    [5, "Other Fee", "0"],
    [6, "Fine Received", "0"],
    ["", "Total Discount", number_format($summary['total_discount'], 2)],
    ["", "Total Amount", number_format($summary['total_amount'], 2)],
    ["", "Total Received ($month Challans)", number_format($summary['total_received'], 2)],
    ["", "Total Receivables", number_format($summary['total_receivables'], 2)]
];

// Write Data
$startRow = 5;
foreach ($dataRows as $rowData) {
    $sheet->fromArray($rowData, null, "A{$startRow}");
    $startRow++;
}

// Style the Table Data
$sheet->getStyle("A5:{$lastColumn}" . ($startRow - 1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]
]);

// Make "Total Receivables" Row Stand Out
$sheet->getStyle("A" . ($startRow - 1) . ":{$lastColumn}" . ($startRow - 1))->applyFromArray([
    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6A5ACD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Set Column Widths Manually for Full A4 Portrait Mode
$columnWidths = [15, 50, 25]; // Adjust widths for better A4 fit
$columnLetters = ['A', 'B', 'C'];
foreach ($columnLetters as $index => $letter) {
    $sheet->getColumnDimension($letter)->setWidth($columnWidths[$index]);
}

// Set Row Heights for Better Spacing
for ($row = 1; $row <= $startRow; $row++) {
    $sheet->getRowDimension($row)->setRowHeight(25);
}

// Output the Excel file
if (ob_get_contents()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Monthly_Fee_Summary_' . $month . '_' . $session . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit();
