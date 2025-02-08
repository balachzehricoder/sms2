<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Database connection
require_once 'confiq.php';

// Retrieve GET filters
$selected_session = isset($_GET['session']) ? trim($_GET['session']) : 'N/A';
$selected_month = isset($_GET['month']) ? trim($_GET['month']) : 'N/A';
$selected_class = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';
$selected_section = isset($_GET['section_id']) ? trim($_GET['section_id']) : '';
$arrears_filter = isset($_GET['arrears_filter']) ? trim($_GET['arrears_filter']) : 'Above Amount';
$arrears_amount = isset($_GET['arrears_amount']) ? floatval($_GET['arrears_amount']) : 0;

// Build the WHERE clause
$where = "1";
if (!empty($selected_session)) $where .= " AND TRIM(challan_session) = TRIM('$selected_session')";
if (!empty($selected_month)) $where .= " AND TRIM(challan_month) = TRIM('$selected_month')";
if (!empty($selected_class)) $where .= " AND s.class_id = '$selected_class'";
if (!empty($selected_section)) $where .= " AND s.section_id = '$selected_section'";
if ($arrears_amount > 0) {
    $comparison = ($arrears_filter == 'Above Amount') ? ">=" : "<=";
    $where .= " AND c.arrears $comparison $arrears_amount";
}

// Fetch Fee Receivable Data
$query = "
    SELECT 
        c.challan_id, 
        c.challan_month, 
        c.challan_session, 
        c.total_amount, 
        c.arrears, 
        c.discount, 
        c.final_amount, 
        c.status, 
        s.student_id, 
        s.student_name, 
        s.family_code,
        cl.class_name, 
        sec.section_name,
        COALESCE(SUM(p.amount_paid), 0) AS amount_paid
    FROM challans c
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    LEFT JOIN payments p ON p.challan_id = c.challan_id
    WHERE $where
    GROUP BY c.challan_id
    ORDER BY c.challan_id DESC";

$result = $conn->query($query);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set sheet title
$sheet->setTitle("Fee Receivable Report");

// Define Headers
$headers = ['Challan ID', 'Student Name', 'Family Code', 'Class', 'Section', 'Month', 'Total Fee', 'Arrears', 'Amount Paid', 'Final Amount', 'Status'];
$totalColumns = count($headers);
$lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

// --- Sheet Title (Row 1) ---
$sheet->setCellValue('A1', 'Fee Receivable Report');
$sheet->mergeCells("A1:{$lastColumn}1");
$sheet->getStyle("A1")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 16],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFD9D9D9']
    ]
]);

// --- Session & Month (Row 2 & 3) --- ✅ FIXED!
$sheet->setCellValue('A2', 'Session:');
$sheet->setCellValue('B2', $selected_session);
$sheet->mergeCells("B2:C2");

$sheet->setCellValue('D2', 'Month:');
$sheet->setCellValue('E2', $selected_month);
$sheet->mergeCells("E2:F2");

// Style for Session & Month
$sheet->getStyle("A2:E2")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
]);

// --- Table Header Row (Row 4) ---
$sheet->fromArray($headers, null, 'A4');
$sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 12],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFE6E6FA']
    ],
    'borders'   => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF000000']
        ]
    ]
]);

// Populate the data rows starting from Row 5
$rowNumber = 5;
while ($data = $result->fetch_assoc()) {
    $sheet->fromArray([
        $data['challan_id'],
        $data['student_name'],
        $data['family_code'],
        $data['class_name'],
        $data['section_name'],
        $data['challan_month'],
        number_format($data['total_amount'], 2),
        number_format($data['arrears'], 2),
        number_format($data['amount_paid'], 2),
        number_format($data['final_amount'], 2),
        ucfirst($data['status'])
    ], null, "A{$rowNumber}");
    $rowNumber++;
}

// Apply borders to the entire table
$sheet->getStyle("A4:{$lastColumn}" . ($rowNumber - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF000000']
        ]
    ]
]);

// Auto-size all columns
for ($col = 1; $col <= $totalColumns; $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

// Output the Excel file
if (ob_get_contents()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Fee_Receivable_Report_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit();
