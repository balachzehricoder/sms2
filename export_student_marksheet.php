<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Database connection
require_once 'confiq.php';

$student_id = intval($_POST['student_id']);
$exam_id = intval($_POST['exam_id']);

// Fetch student details
$studentQuery = "SELECT student_name, class_id, section_id FROM students WHERE student_id = $student_id";
$studentResult = $conn->query($studentQuery);
$student = $studentResult->fetch_assoc();

// Fetch class and section names
$classQuery = "SELECT class_name FROM classes WHERE class_id = {$student['class_id']}";
$classResult = $conn->query($classQuery)->fetch_assoc();
$sectionQuery = "SELECT section_name FROM sections WHERE section_id = {$student['section_id']}";
$sectionResult = $conn->query($sectionQuery)->fetch_assoc();

// Fetch exam name
$examQuery = "SELECT exam_name FROM exams WHERE exam_id = $exam_id";
$examResult = $conn->query($examQuery)->fetch_assoc();

// Fetch marks
$marksQuery = "
    SELECT sub.subject_name, em.marks_obtained, em.max_marks
    FROM exam_marks em
    JOIN subjects sub ON em.subject_id = sub.subject_id
    WHERE em.student_id = $student_id AND em.exam_id = $exam_id
    ORDER BY sub.subject_name";
$marksResult = $conn->query($marksQuery);

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header: School Name
$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', 'Hazara Academy School');
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 20],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Sub-header: Exam Name
$sheet->mergeCells('A2:E2');
$sheet->setCellValue('A2', "Marksheet for: " . $examResult['exam_name']);
$sheet->getStyle('A2')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Student Details
$sheet->setCellValue('A4', 'Student Name:');
$sheet->setCellValue('B4', $student['student_name']);
$sheet->setCellValue('A5', 'Class:');
$sheet->setCellValue('B5', $classResult['class_name']);
$sheet->setCellValue('A6', 'Section:');
$sheet->setCellValue('B6', $sectionResult['section_name']);

// Apply bold to labels
$sheet->getStyle('A4:A6')->getFont()->setBold(true);

// Add empty row before marks table
$startRow = 8;

// Table Headers
$headers = ['Subject', 'Marks Obtained', 'Total Marks', 'Percentage', 'Grade'];
$sheet->fromArray($headers, null, "A{$startRow}");

// Style the header
$sheet->getStyle("A{$startRow}:E{$startRow}")->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDDDDD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Add Marks Data
$rowNum = $startRow + 1;
$totalObtained = 0;
$totalMax = 0;

if ($marksResult->num_rows > 0) {
    while ($row = $marksResult->fetch_assoc()) {
        $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
        $grade = '';

        // Assign grade
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

        $sheet->setCellValue("A{$rowNum}", $row['subject_name']);
        $sheet->setCellValue("B{$rowNum}", $row['marks_obtained']);
        $sheet->setCellValue("C{$rowNum}", $row['max_marks']);
        $sheet->setCellValue("D{$rowNum}", number_format($percentage, 2) . '%');
        $sheet->setCellValue("E{$rowNum}", $grade);

        // Style for each row
        $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $totalObtained += $row['marks_obtained'];
        $totalMax += $row['max_marks'];
        $rowNum++;
    }
}

// Add Overall Performance
$overallPercentage = ($totalObtained / $totalMax) * 100;
$overallGrade = '';

if ($overallPercentage >= 90) {
    $overallGrade = 'A+';
} elseif ($overallPercentage >= 80) {
    $overallGrade = 'A';
} elseif ($overallPercentage >= 70) {
    $overallGrade = 'B';
} elseif ($overallPercentage >= 60) {
    $overallGrade = 'C';
} elseif ($overallPercentage >= 50) {
    $overallGrade = 'D';
} else {
    $overallGrade = 'F';
}

$sheet->setCellValue("A{$rowNum}", 'Total');
$sheet->setCellValue("B{$rowNum}", $totalObtained);
$sheet->setCellValue("C{$rowNum}", $totalMax);
$sheet->setCellValue("D{$rowNum}", number_format($overallPercentage, 2) . '%');
$sheet->setCellValue("E{$rowNum}", $overallGrade);

// Style for Total Row
$sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEEE8AA']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Auto-size columns
foreach (range('A', 'E') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Clear output buffer
if (ob_get_contents()) ob_end_clean();

// Output file
$fileName = "{$student['student_name']}_Marksheet.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
