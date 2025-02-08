<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

require_once 'confiq.php';

$selected_session = isset($_GET['session']) ? trim($_GET['session']) : '';
$selected_class = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';
$selected_section = isset($_GET['section_id']) ? trim($_GET['section_id']) : '';
$from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

$where = "1";
if (!empty($selected_session)) $where .= " AND TRIM(c.challan_session) = TRIM('$selected_session')";
if (!empty($selected_class)) $where .= " AND s.class_id = '$selected_class'";
if (!empty($selected_section)) $where .= " AND s.section_id = '$selected_section'";
if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND p.payment_date BETWEEN '$from_date' AND '$to_date'";
}

// Fetch Fee Collection Data
$query = "
    SELECT 
        c.challan_id, 
        s.student_name, 
        s.family_code, 
        cl.class_name, 
        sec.section_name, 
        c.challan_month,
        c.due_date,
        c.total_amount, 
        c.arrears, 
        c.discount, 
        c.final_amount, 
        COALESCE(SUM(p.amount_paid), 0) AS amount_paid,
        c.status
    FROM challans c
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    LEFT JOIN payments p ON p.challan_id = c.challan_id
    WHERE $where
    GROUP BY c.challan_id
    ORDER BY cl.class_name, sec.section_name, s.student_name ASC";

$result = $conn->query($query);

// Fetch Summary Data
$summary_query = "
    SELECT 
        SUM(c.total_amount) AS total_fees,
        SUM(c.discount) AS total_discounts,
        SUM(c.arrears) AS total_arrears,
        SUM(p.amount_paid) AS total_collected,
        (SUM(c.final_amount) - SUM(p.amount_paid)) AS total_remaining,
        COUNT(DISTINCT CASE WHEN c.status = 'paid' THEN c.student_id END) AS total_paid_students,
        COUNT(DISTINCT CASE WHEN c.status != 'paid' THEN c.student_id END) AS total_unpaid_students
    FROM challans c
    LEFT JOIN payments p ON p.challan_id = c.challan_id
    WHERE $where";

$summary_result = $conn->query($summary_query);
$summary = ($summary_result) ? $summary_result->fetch_assoc() : [];

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Fee Collection Report");

$headers = ['S.No', 'Student Name', 'Family Code', 'Class', 'Section', 'Month', 'Due Date', 'Total Fee', 'Arrears', 'Discount', 'Final Amount', 'Amount Paid', 'Status'];
$totalColumns = count($headers);
$lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

// Report Title
$sheet->setCellValue('A1', 'Fee Collection Report');
$sheet->mergeCells("A1:{$lastColumn}1");
$sheet->getStyle("A1")->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Filters Info
$sheet->setCellValue('A2', "Session: $selected_session | Class: " . ($selected_class ?: 'All') . " | Section: " . ($selected_section ?: 'All') . " | From: $from_date | To: $to_date");
$sheet->mergeCells("A2:{$lastColumn}2");
$sheet->getStyle("A2")->applyFromArray([
    'font' => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Header Row
$sheet->fromArray($headers, null, 'A4');
$sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6E6FA']]
]);

// Populate Table Data
$rowNumber = 5;
$serial = 1;
while ($data = $result->fetch_assoc()) {
    $sheet->fromArray([
        $serial++,
        $data['student_name'],
        $data['family_code'],
        $data['class_name'],
        $data['section_name'],
        $data['challan_month'],
        $data['due_date'],
        number_format($data['total_amount'], 2),
        number_format($data['arrears'], 2),
        number_format($data['discount'], 2),
        number_format($data['final_amount'], 2),
        number_format($data['amount_paid'], 2),
        ucfirst($data['status'])
    ], null, "A{$rowNumber}");
    $rowNumber++;
}

// Apply Borders to Table
$sheet->getStyle("A4:{$lastColumn}" . ($rowNumber - 1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Auto-size Columns
foreach (range('A', $lastColumn) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Summary Section
$summaryRow = $rowNumber + 2;
$sheet->setCellValue("A{$summaryRow}", "Total Paid Students:");
$sheet->setCellValue("B{$summaryRow}", $summary['total_paid_students'] ?? 0);

$sheet->setCellValue("A" . ($summaryRow + 1), "Total Unpaid Students:");
$sheet->setCellValue("B" . ($summaryRow + 1), $summary['total_unpaid_students'] ?? 0);

$sheet->setCellValue("A" . ($summaryRow + 2), "Total Fees:");
$sheet->setCellValue("B" . ($summaryRow + 2), number_format($summary['total_fees'] ?? 0, 2));

$sheet->setCellValue("A" . ($summaryRow + 3), "Total Discounts:");
$sheet->setCellValue("B" . ($summaryRow + 3), number_format($summary['total_discounts'] ?? 0, 2));

$sheet->setCellValue("A" . ($summaryRow + 4), "Total Arrears:");
$sheet->setCellValue("B" . ($summaryRow + 4), number_format($summary['total_arrears'] ?? 0, 2));

$sheet->getStyle("A{$summaryRow}:B" . ($summaryRow + 4))->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
]);

// Clear Output Buffer to Avoid Corrupt File
if (ob_get_length()) ob_end_clean();

// Download File
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Fee_Collection_Report_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit();
