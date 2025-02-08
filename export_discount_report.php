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
$selected_session = isset($_GET['session']) ? trim($_GET['session']) : '';
$selected_month = isset($_GET['month']) ? trim($_GET['month']) : '';
$selected_class = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';
$selected_section = isset($_GET['section_id']) ? trim($_GET['section_id']) : '';

// Build WHERE clause
$where = "c.discount > 0"; // Only fetch students who received a discount
if (!empty($selected_session)) $where .= " AND TRIM(c.challan_session) = TRIM('$selected_session')";
if (!empty($selected_month)) $where .= " AND TRIM(c.challan_month) = TRIM('$selected_month')";
if (!empty($selected_class)) $where .= " AND s.class_id = '$selected_class'";
if (!empty($selected_section)) $where .= " AND s.section_id = '$selected_section'";

// Fetch Discount Data
$query = "
    SELECT 
        s.student_name,
        s.father_name,
        cl.class_name,
        sec.section_name,
        c.challan_month,
        c.discount
    FROM challans c
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE $where
    ORDER BY cl.class_name, sec.section_name, s.student_name ASC";

$result = $conn->query($query);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set sheet title
$sheet->setTitle("Discount Report");

// Define Headers
$headers = ['S.No', 'Student Name', 'Father Name', 'Class', 'Section', 'Month', 'Discount Amount'];
$totalColumns = count($headers);
$lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

// --- Report Title ---
$sheet->setCellValue('A1', 'Monthly Discount Report');
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

// --- Session & Month Row ---
$sheet->setCellValue('A2', "Session: $selected_session | Month: $selected_month");
$sheet->mergeCells("A2:{$lastColumn}2");
$sheet->getStyle("A2")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ]
]);

// --- Table Header Row ---
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
$serialNumber = 1;

while ($data = $result->fetch_assoc()) {
    $sheet->fromArray([
        $serialNumber,
        $data['student_name'],
        $data['father_name'],
        $data['class_name'],
        $data['section_name'],
        $data['challan_month'],
        number_format($data['discount'], 2)
    ], null, "A{$rowNumber}");

    $serialNumber++;
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
header('Content-Disposition: attachment; filename="Monthly_Discount_Report_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit();
