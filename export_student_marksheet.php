<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

// Database connection
require_once 'confiq.php';

$student_id = intval($_POST['student_id']);
$exam_id = intval($_POST['exam_id']);

// Fetch student details
$studentQuery = "SELECT student_name, family_code, father_name, class_id, section_id FROM students WHERE student_id = $student_id";
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

// Fetch performance criteria with remarks
$performanceQuery = "
    SELECT pc.criteria_name, cr.remark
    FROM student_performance sp
    JOIN performance_criteria pc ON sp.criteria_id = pc.id
    JOIN criteria_remarks cr ON sp.score = cr.score
    WHERE sp.student_id = $student_id";
$performanceResult = $conn->query($performanceQuery);

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Adjust column widths
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(15);

// Header Section
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'HAZARA PUBLIC SCHOOL & COLLEGE JAMBER');
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', 'Result Card ANNUAL EXAM 2024-25');
$sheet->getStyle('A2')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Student Information
$sheet->setCellValue('A4', 'Family Code:');
$sheet->setCellValue('B4', $student['family_code']);
$sheet->setCellValue('D4', 'Class:');
$sheet->setCellValue('E4', $classResult['class_name'] . ' (' . $sectionResult['section_name'] . ')');

$sheet->setCellValue('A5', 'Name:');
$sheet->setCellValue('B5', $student['student_name']);
$sheet->setCellValue('A6', 'Father Name:');
$sheet->setCellValue('B6', $student['father_name']);

// Styling for student details
$sheet->getStyle('A4:A6')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

$sheet->getStyle('B4:B6')->applyFromArray([
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

$sheet->getStyle('D4')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);


// Result Overview Table
$sheet->mergeCells('A8:F8');
$sheet->setCellValue('A8', 'Result Overview');
$sheet->getStyle('A8')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDDDDD']]
]);

$headers = ['Subject', 'Total Marks', 'Obt. Marks', '%', 'Grade', 'Result'];
$sheet->fromArray($headers, null, 'A9');
$sheet->getStyle('A9:F9')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDDDDD']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Fill Marks Data
$rowNum = 10;
$totalObtained = 0;
$totalMax = 0;
$subjectNames = [];
$marksObtained = [];

while ($row = $marksResult->fetch_assoc()) {
    $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
    $grade = $percentage >= 90 ? 'A+' : ($percentage >= 80 ? 'A' : ($percentage >= 70 ? 'B' : ($percentage >= 60 ? 'C' : ($percentage >= 50 ? 'D' : 'F'))));
    $resultStatus = $percentage >= 50 ? 'Passed' : 'Failed';

    $sheet->fromArray([$row['subject_name'], $row['max_marks'], $row['marks_obtained'], number_format($percentage, 2) . '%', $grade, $resultStatus], null, "A{$rowNum}");

    $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    $totalObtained += $row['marks_obtained'];
    $totalMax += $row['max_marks'];

    $subjectNames[] = $row['subject_name'];
    $marksObtained[] = $row['marks_obtained'];

    $rowNum++;
}

// Total Row
$overallPercentage = ($totalObtained / $totalMax) * 100;
$overallGrade = $overallPercentage >= 90 ? 'A+' : ($overallPercentage >= 80 ? 'A' : ($overallPercentage >= 70 ? 'B' : ($overallPercentage >= 60 ? 'C' : ($overallPercentage >= 50 ? 'D' : 'F'))));

$sheet->fromArray(['Total', $totalMax, $totalObtained, number_format($overallPercentage, 2) . '%', $overallGrade], null, "A{$rowNum}");
$sheet->mergeCells("A{$rowNum}:A{$rowNum}");
$sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0E0E0']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Percentage Remarks
$sheet->mergeCells("A" . ($rowNum + 2) . ":F" . ($rowNum + 2));
$sheet->setCellValue("A" . ($rowNum + 2), "Congratulations on your exceptional achievement and earning an $overallGrade grade – your hard work truly paid off!");
$sheet->getStyle("A" . ($rowNum + 2))->applyFromArray([
    'font' => ['italic' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Performance Criteria (with Remarks)
$criteriaHeaders = [];
$criteriaRemarks = [];

while ($criteria = $performanceResult->fetch_assoc()) {
    $criteriaHeaders[] = $criteria['criteria_name'];   // Criteria Name
    $criteriaRemarks[] = $criteria['remark'];          // Corresponding Remark
}

$performanceRowStart = $rowNum + 4;
$sheet->fromArray($criteriaHeaders, null, "A{$performanceRowStart}");
$sheet->fromArray($criteriaRemarks, null, "A" . ($performanceRowStart + 1));

$sheet->getStyle("A{$performanceRowStart}:" . chr(64 + count($criteriaHeaders)) . ($performanceRowStart + 1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'font' => ['bold' => true]
]);

// Instructions Section - Vertical Display
$instructions = [
    "If found any error then contact admin office within 5 days from issuing date.",
    "Annual position based on First, Second and Third term exam.",
    "Fail in more than 2 subjects will consider student as Fail."
];

$instructionRowStart = $rowNum + 7;
foreach ($instructions as $index => $instruction) {
    $sheet->setCellValue("A" . ($instructionRowStart + $index), $instruction);
    $sheet->mergeCells("A" . ($instructionRowStart + $index) . ":F" . ($instructionRowStart + $index));
}

$sheet->getStyle("A" . ($instructionRowStart) . ":F" . ($instructionRowStart + count($instructions)))->applyFromArray([
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

// Create Bar Chart for Marks Below the Table
$labels = [new DataSeriesValues('String', "'Worksheet'!\$A\$10:\$A\$" . ($rowNum - 1), null, count($subjectNames))];
$values = [new DataSeriesValues('Number', "'Worksheet'!\$C\$10:\$C\$" . ($rowNum - 1), null, count($marksObtained))];

$series = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    range(0, count($marksObtained) - 1),
    [],
    $labels,
    $values
);

$plotArea = new PlotArea(null, [$series]);
$chart = new Chart(
    'Marks Chart',
    new Title('Subject-wise Marks Distribution'),
    new Legend(Legend::POSITION_BOTTOM, null, false),
    $plotArea
);

$chart->setTopLeftPosition('A' . ($instructionRowStart + count($instructions) + 2));
$chart->setBottomRightPosition('F' . ($instructionRowStart + count($instructions) + 20));
$sheet->addChart($chart);

// Clear buffer and output the file
if (ob_get_contents()) ob_end_clean();
$fileName = "{$student['student_name']}_Marksheet.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$fileName\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;
