<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Database connection
include 'confiq.php';

// Retrieve filter values from the POST request
$class_id = intval($_POST['class_id']);
$section_id = intval($_POST['section_id']);
$session_id = intval($_POST['session_id']);
$exam_id = intval($_POST['exam_id']);
$subject_id = intval($_POST['subject_id']);

// Fetch data from the database
$query = "
    SELECT s.student_name, em.marks_obtained, em.max_marks
    FROM exam_marks em
    JOIN students s ON em.student_id = s.student_id
    WHERE s.class_id = $class_id 
      AND s.section_id = $section_id 
      AND s.session = $session_id 
      AND em.exam_id = $exam_id 
      AND em.subject_id = $subject_id
    ORDER BY s.student_name";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the header row
$headers = ['Student Name', 'Marks Obtained', 'Total Marks', 'Percentage', 'Grade'];
$sheet->fromArray($headers, null, 'A1');

// Apply styles to the header row
$headerStyle = [
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE699']]
];
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

// Add data starting from the second row
$rowNumber = 2;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;

        // Determine grade based on percentage
        if ($percentage >= 90) {
            $grade = 'A+';
        } elseif ($percentage >= 80) {
            $grade = 'A';
        } elseif ($percentage >= 70) {
            $grade = 'B';
        } elseif ($percentage >= 60) {
            $grade = 'C';
        } elseif ($percentage >= 50) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }

        // Add student data to the sheet
        $sheet->fromArray(
            [
                $row['student_name'],
                $row['marks_obtained'],
                $row['max_marks'],
                number_format($percentage, 2) . '%',
                $grade
            ],
            null,
            "A{$rowNumber}"
        );
        $rowNumber++;
    }
} else {
    $sheet->setCellValue('A2', 'No data available');
}

// Apply borders to all cells and auto-size columns
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

// Generate the file name
$fileName = "grades_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

// Write and output the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Close the database connection
$conn->close();
exit;
