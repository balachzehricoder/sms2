<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Database connection
include 'confiq.php';

// Fetch data from the database
$studentQuery = "SELECT student_id, family_code, student_name, session, class_id, section_id, gender, religion, dob, date_of_admission, father_cell_no, mother_cell_no, state, city, email, father_name, mother_name, home_address, created_at FROM students ORDER BY student_name ASC";
$studentResult = $conn->query($studentQuery);

if (!$studentResult) {
    die("Query failed: " . $conn->error);
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the header row
$headers = [
    'Student ID', 'Family Code', 'Student Name', 'Session', 'Class ID', 'Section ID', 
    'Gender', 'Religion', 'Date of Birth', 'Date of Admission', 
    "Father's Cell No", "Mother's Cell No", 'State', 'City', 'Email', 
    "Father's Name", "Mother's Name", 'Home Address', 'Created At'
];

// Add headers to the first row
$sheet->fromArray($headers, null, 'A1');

// Apply styles to the header row
$headerStyle = [
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE699']]
];
$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

// Add data starting from the second row
$rowNumber = 2; // Start from the second row
if ($studentResult->num_rows > 0) {
    while ($row = $studentResult->fetch_assoc()) {
        $sheet->fromArray(array_values($row), null, "A{$rowNumber}");
        $rowNumber++;
    }
} else {
    $sheet->setCellValue('A2', 'No data available');
}

// Apply borders and auto-size to columns
$highestColumn = $sheet->getHighestColumn();
$highestRow = $sheet->getHighestRow();

$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => '000000'],
        ],
    ],
];
$sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray($styleArray);

foreach (range('A', $highestColumn) as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Clear any existing output buffer
if (ob_get_contents()) {
    ob_end_clean();
}

// Save the spreadsheet as an Excel file
$fileName = "students_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

// Write the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Close the database connection
$conn->close();
exit;
?>
