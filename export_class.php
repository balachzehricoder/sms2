<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Database connection
include 'confiq.php';

// Get class ID from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id === 0) {
    die("Invalid Class ID.");
}

// Fetch class details
$classQuery = "SELECT class_name FROM classes WHERE class_id = ?";
$stmt = $conn->prepare($classQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classDetails = $result->fetch_assoc();

if (!$classDetails) {
    die("Class not found.");
}

// Fetch students for the class
$studentQuery = "SELECT 
                    student_id, family_code, student_name, gender, father_name, 
                    father_cell_no, dob, date_of_admission, session_name, religion
                 FROM students 
                 JOIN sessions ON students.session = sessions.id
                 WHERE class_id = ? AND status = 'active'
                 ORDER BY student_name ASC";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$studentsResult = $stmt->get_result();

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set title and headers
$title = "Class Details: " . htmlspecialchars($classDetails['class_name']);
$headers = [
    'S.No', 'Student ID', 'Family Code', 'Student Name', 'Gender', 
    "Father's Name", "Father's Cell No", 'Date of Birth', 'Date of Admission', 
    'Session', 'Religion'
];

// Set title row
$sheet->setCellValue('A1', $title);
$sheet->mergeCells('A1:K1');
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Add headers to the second row
$sheet->fromArray($headers, null, 'A2');

// Apply styles to the header row
$headerStyle = [
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE699']]
];
$sheet->getStyle('A2:K2')->applyFromArray($headerStyle);

// Add data starting from the third row
$rowNumber = 3;
$sno = 1;

if ($studentsResult->num_rows > 0) {
    while ($row = $studentsResult->fetch_assoc()) {
        $data = [
            $sno++, $row['student_id'], $row['family_code'], $row['student_name'], 
            $row['gender'], $row['father_name'], $row['father_cell_no'], 
            $row['dob'], $row['date_of_admission'], $row['session_name'], 
            $row['religion']
        ];
        $sheet->fromArray($data, null, "A{$rowNumber}");
        $rowNumber++;
    }
} else {
    $sheet->setCellValue('A3', 'No data available');
    $sheet->mergeCells('A3:K3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
$sheet->getStyle("A2:{$highestColumn}{$highestRow}")->applyFromArray($styleArray);

foreach (range('A', $highestColumn) as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Clear any existing output buffer
if (ob_get_contents()) {
    ob_end_clean();
}

// Save the spreadsheet as an Excel file
$fileName = "class_" . htmlspecialchars($classDetails['class_name']) . "_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

// Write the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Close the database connection
$conn->close();
exit;
