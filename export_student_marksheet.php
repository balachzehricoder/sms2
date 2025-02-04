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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Database connection
require_once 'confiq.php';

$student_id = intval($_POST['student_id']);
$exam_id    = intval($_POST['exam_id']);

// Fetch student details (including student_image)
$studentQuery = "SELECT student_name, family_code, father_name, class_id, section_id, student_image FROM students WHERE student_id = $student_id";
$studentResult = $conn->query($studentQuery);
$student = $studentResult->fetch_assoc();

// Fetch class and section names
$classQuery   = "SELECT class_name FROM classes WHERE class_id = {$student['class_id']}";
$classResult  = $conn->query($classQuery)->fetch_assoc();
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

// Fetch signature record (adjust the query if needed)
$signatureQuery = "SELECT exam_head_signature, principal_signature FROM signatures LIMIT 1";
$signatureResult = $conn->query($signatureQuery)->fetch_assoc();

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Adjust column widths (using columns A through F)
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(15);

// ========= Add Images =========

// -- School Logo (placed in A1) --
$drawingLogo = new Drawing();
$drawingLogo->setName('School Logo');
$drawingLogo->setDescription('School Logo');
$drawingLogo->setPath('images/logo.png'); // update with your actual logo path
$drawingLogo->setHeight(100);  // Enlarged logo
$drawingLogo->setCoordinates('A1');
$drawingLogo->setOffsetX(5);
$drawingLogo->setOffsetY(5);
$drawingLogo->setWorksheet($sheet);

// -- Student Image (placed at F5 so it appears at the right of student info) --
if (!empty($student['student_image']) && file_exists($student['student_image'])) {
    $drawingStudent = new Drawing();
    $drawingStudent->setName('Student Image');
    $drawingStudent->setDescription('Student Image');
    $drawingStudent->setPath($student['student_image']);
    $drawingStudent->setHeight(70);  // Adjusted image height
    $drawingStudent->setCoordinates('F5');
    $drawingStudent->setOffsetX(5);
    $drawingStudent->setOffsetY(5);
    $drawingStudent->setWorksheet($sheet);
}

// ========= Header Section (starting at row 1) =========

// Row 1: Merge B1:F1 for School Name (logo is in A1)
$sheet->mergeCells('B1:F1');
$sheet->setCellValue('B1', 'HAZARA PUBLIC SCHOOL & COLLEGE JAMBER');
$sheet->getStyle('B1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Row 2: School Contact Information (merged across A2:F2)
$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', "Affiliated with Lahore Board | Award for Performance (Govt. of Pakistan) Boys H/S 11152 | Girls H/S 12103\nBoys College 1129 | Girls College 1225 Hazara Road, Jamber, Tehsil Pattoki, District Kasur\nPunjab, Pakistan www.hazara.edu.pk");
$sheet->getStyle('A2')->applyFromArray([
    'font' => ['size' => 10],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true
    ],
]);

// Row 3: Contact Numbers (merged A3:F3)
$sheet->mergeCells('A3:F3');
$sheet->setCellValue('A3', '03000132470 | 03005353470 | info@hazara.edu.pk');
$sheet->getStyle('A3')->applyFromArray([
    'font' => ['size' => 10],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Row 4: Result Card Title (merged A4:F4)
$sheet->mergeCells('A4:F4');
$sheet->setCellValue('A4', 'Result Card ANNUAL EXAM 2024-25');
$sheet->getStyle('A4')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// ========= Student Information =========
// Row 5: Family Code and Class
$sheet->setCellValue('A5', 'Family Code:');
$sheet->setCellValue('B5', $student['family_code']);
$sheet->setCellValue('D5', 'Class:');
$sheet->setCellValue('E5', $classResult['class_name'] . ' (' . $sectionResult['section_name'] . ')');

// Row 6: Name
$sheet->setCellValue('A6', 'Name:');
$sheet->setCellValue('B6', $student['student_name']);

// Row 7: Father Name
$sheet->setCellValue('A7', 'Father Name:');
$sheet->setCellValue('B7', $student['father_name']);

$sheet->getStyle('A5:A7')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);
$sheet->getStyle('B5:B7')->applyFromArray([
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);
$sheet->getStyle('D5')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

// ========= Result Overview Table =========
// We now start the result table 2 rows below the current start.
// Previously, it started at row 8; now we set it to row 10.
$startRow = 10;
$sheet->mergeCells("A{$startRow}:F{$startRow}");
$sheet->setCellValue("A{$startRow}", 'Result Overview');
$sheet->getStyle("A{$startRow}")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDDDDD']]
]);

// Table Headers at row 11
$headers = ['Subject', 'Total Marks', 'Obt. Marks', '%', 'Grade', 'Result'];
$sheet->fromArray($headers, null, 'A' . ($startRow + 1));
$sheet->getStyle("A" . ($startRow + 1) . ":F" . ($startRow + 1))->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDDDDD']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Fill Marks Data starting at row 12
$rowNum = $startRow + 2;
$totalObtained = 0;
$totalMax = 0;
$subjectNames = [];
$marksObtained = [];

while ($row = $marksResult->fetch_assoc()) {
    $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
    $grade = $percentage >= 90 ? 'A+' : ($percentage >= 80 ? 'A' : ($percentage >= 70 ? 'B' : ($percentage >= 60 ? 'C' : ($percentage >= 50 ? 'D' : 'F'))));
    $resultStatus = $percentage >= 50 ? 'Passed' : 'Failed';

    $sheet->fromArray(
        [$row['subject_name'], $row['max_marks'], $row['marks_obtained'], number_format($percentage, 2) . '%', $grade, $resultStatus],
        null,
        "A{$rowNum}"
    );
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

// Percentage Remarks (next row)
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
    $criteriaHeaders[] = $criteria['criteria_name'];
    $criteriaRemarks[] = $criteria['remark'];
}
$performanceRowStart = $rowNum + 4;
$sheet->fromArray($criteriaHeaders, null, "A{$performanceRowStart}");
$sheet->fromArray($criteriaRemarks, null, "A" . ($performanceRowStart + 1));
$lastCol = chr(64 + count($criteriaHeaders));
$sheet->getStyle("A{$performanceRowStart}:{$lastCol}" . ($performanceRowStart + 1))->applyFromArray([
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

// Create Bar Chart for Marks
$labels = [new DataSeriesValues('String', "'Worksheet'!\$A\$" . ($startRow + 2) . ":\$A\$" . ($rowNum - 1), null, count($subjectNames))];
$values = [new DataSeriesValues('Number', "'Worksheet'!\$C\$" . ($startRow + 2) . ":\$C\$" . ($rowNum - 1), null, count($marksObtained))];
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
// Position the chart below the instructions; here we use column G for extra width.
$chartTopRow = $instructionRowStart + count($instructions) + 2;
$chart->setTopLeftPosition("A{$chartTopRow}");
$chart->setBottomRightPosition("G" . ($chartTopRow + 12));
$sheet->addChart($chart);

// ========= Add Signatures =========
// Position the signatures below the chart.
$signatureRow = $chartTopRow + 14;
if (!empty($signatureResult['exam_head_signature']) && file_exists($signatureResult['exam_head_signature'])) {
    $drawingExamSignature = new Drawing();
    $drawingExamSignature->setName('Exam Head Signature');
    $drawingExamSignature->setDescription('Exam Head Signature');
    $drawingExamSignature->setPath($signatureResult['exam_head_signature']);
    $drawingExamSignature->setHeight(50);
    $drawingExamSignature->setCoordinates("B{$signatureRow}");
    $drawingExamSignature->setOffsetX(10);
    $drawingExamSignature->setWorksheet($sheet);
}
if (!empty($signatureResult['principal_signature']) && file_exists($signatureResult['principal_signature'])) {
    $drawingPrincipalSignature = new Drawing();
    $drawingPrincipalSignature->setName('Principal Signature');
    $drawingPrincipalSignature->setDescription('Principal Signature');
    $drawingPrincipalSignature->setPath($signatureResult['principal_signature']);
    $drawingPrincipalSignature->setHeight(50);
    $drawingPrincipalSignature->setCoordinates("E{$signatureRow}");
    $drawingPrincipalSignature->setOffsetX(10);
    $drawingPrincipalSignature->setWorksheet($sheet);
}
$sheet->setCellValue("B" . ($signatureRow + 4), "Exam Head");
$sheet->setCellValue("E" . ($signatureRow + 4), "Principal");
$sheet->getStyle("B" . ($signatureRow + 4) . ":E" . ($signatureRow + 4))->getFont()->setBold(true);
$sheet->getStyle("B" . ($signatureRow + 4) . ":E" . ($signatureRow + 4))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Output the file
if (ob_get_contents()) ob_end_clean();
$fileName = "{$student['student_name']}_Marksheet.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$fileName\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;
