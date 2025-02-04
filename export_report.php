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

// Retrieve GET data (for teacher, session, and exam)
$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
$exam_id    = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

// Fetch Teacher Name
$teacherQuery = "SELECT teacher_name FROM teachers WHERE teacher_id = $teacher_id";
$teacherResult = $conn->query($teacherQuery) or die("Error fetching teacher: " . $conn->error);
$teacherRow = $teacherResult->fetch_assoc();

// Fetch Performance Data
$performanceQuery = "
SELECT
    s.subject_name AS Subject,
    t.teacher_name AS Teacher,
    c.class_name AS Class,
    sec.section_name AS Section,
    COUNT(CASE WHEN em.marks_obtained >= 90 THEN 1 END) AS A,
    COUNT(CASE WHEN em.marks_obtained >= 80 AND em.marks_obtained < 90 THEN 1 END) AS B,
    COUNT(CASE WHEN em.marks_obtained >= 70 AND em.marks_obtained < 80 THEN 1 END) AS C,
    COUNT(CASE WHEN em.marks_obtained >= 60 AND em.marks_obtained < 70 THEN 1 END) AS D,
    COUNT(CASE WHEN em.marks_obtained < 60 OR em.marks_obtained IS NULL THEN 1 END) AS F,
    COUNT(*) AS Total_Students,
    ROUND(AVG(em.marks_obtained), 2) AS Average_Marks
FROM
    teacher_subjects ts
JOIN teachers t ON ts.teacher_id = t.teacher_id
JOIN subjects s ON ts.subject_id = s.subject_id
JOIN students stu ON ts.class_id = stu.class_id
JOIN sections sec ON stu.section_id = sec.section_id
JOIN classes c ON stu.class_id = c.class_id
LEFT JOIN exam_marks em 
    ON em.student_id = stu.student_id 
    AND em.subject_id = s.subject_id
    AND em.exam_id = $exam_id
WHERE
    ts.teacher_id = $teacher_id AND stu.session = $session_id
GROUP BY
    s.subject_name,
    t.teacher_name,
    c.class_name,
    sec.section_name
ORDER BY
    s.subject_name,
    c.class_name,
    sec.section_name";

// Run the query and fetch data
$performanceResult = $conn->query($performanceQuery) or die("Error fetching performance data: " . $conn->error);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Build the headers for the table
$headers = ['Subject', 'Teacher', 'Class', 'Section', 'A (90+)', 'B (80-89)', 'C (70-79)', 'D (60-69)', 'F (<60)', 'Total Students', 'Class Average Marks', 'Class Average Grade'];

// Total number of columns and last column letter
$totalColumns = count($headers);
$lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

// --- School Header (Row 1) ---
$sheet->setCellValue('A1', 'Hazara Public School & College Jamber');
$sheet->mergeCells("A1:{$lastColumn}1");
$sheet->getStyle("A1")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF000080']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFE6E6FA']
    ]
]);

// --- Sheet Title (Row 2) ---
$sheet->setCellValue('A2', 'TEACHER PERFORMANCE REPORT');
$sheet->mergeCells("A2:{$lastColumn}2");
$sheet->getStyle("A2")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFB0C4DE']
    ]
]);

// --- Information Row (Row 3) ---
$sheet->setCellValue('A3', "Teacher: {$teacherRow['teacher_name']}");
$sheet->mergeCells("A3:{$lastColumn}3");
$sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ]
]);

// --- Table Header Row (Row 5) ---
$sheet->fromArray($headers, null, 'A5');
$sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 12],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFD9D9D9']
    ],
    'borders'   => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF000000']
        ]
    ]
]);

// Populate the data rows starting from Row 6
$rowNumber = 6;
while ($data = $performanceResult->fetch_assoc()) {
    $sheet->setCellValue("A{$rowNumber}", $data['Subject'])
        ->setCellValue("B{$rowNumber}", $data['Teacher'])
        ->setCellValue("C{$rowNumber}", $data['Class'])
        ->setCellValue("D{$rowNumber}", $data['Section'])
        ->setCellValue("E{$rowNumber}", $data['A'])
        ->setCellValue("F{$rowNumber}", $data['B'])
        ->setCellValue("G{$rowNumber}", $data['C'])
        ->setCellValue("H{$rowNumber}", $data['D'])
        ->setCellValue("I{$rowNumber}", $data['F'])
        ->setCellValue("J{$rowNumber}", $data['Total_Students'])
        ->setCellValue("K{$rowNumber}", $data['Average_Marks']);

    // Calculate and set the grade for the class average
    $average_marks = $data['Average_Marks'];
    $grade = '';
    if ($average_marks >= 90) {
        $grade = 'A+';
    } elseif ($average_marks >= 80) {
        $grade = 'A';
    } elseif ($average_marks >= 70) {
        $grade = 'B+';
    } elseif ($average_marks >= 60) {
        $grade = 'B';
    } elseif ($average_marks >= 50) {
        $grade = 'C+';
    } elseif ($average_marks >= 40) {
        $grade = 'C';
    } elseif ($average_marks >= 33) {
        $grade = 'D+';
    } elseif ($average_marks >= 20) {
        $grade = 'D';
    } else {
        $grade = 'Fail';
    }

    // Add Grade to the Excel file
    $sheet->setCellValue("L{$rowNumber}", $grade);
    $rowNumber++;
}

// Apply borders to the entire table
$sheet->getStyle("A5:{$lastColumn}" . ($rowNumber - 1))->applyFromArray([
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
header('Content-Disposition: attachment; filename="Teacher_Performance_Report_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit();
