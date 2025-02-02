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

// Retrieve POST data
$session_id = intval($_POST['session_id']);
$class_id   = intval($_POST['class_id']);
$section_id = intval($_POST['section_id']);
$exam_id    = intval($_POST['exam_id']);

// Fetch Class, Section, Session, and Exam Info
$classInfo   = $conn->query("SELECT class_name FROM classes WHERE class_id = $class_id")->fetch_assoc();
$sectionInfo = $conn->query("SELECT section_name FROM sections WHERE section_id = $section_id")->fetch_assoc();
$sessionInfo = $conn->query("SELECT session_name FROM sessions WHERE id = $session_id")->fetch_assoc();
$examInfo    = $conn->query("SELECT exam_name FROM exams WHERE exam_id = $exam_id")->fetch_assoc();

// Fetch Students with Family Code
$studentsQuery = "
    SELECT student_id, student_name, family_code, home_cell_no AS cell_number
    FROM students 
    WHERE session = $session_id 
      AND class_id = $class_id 
      AND section_id = $section_id 
    ORDER BY student_name";
$studentsResult = $conn->query($studentsQuery);

// Fetch Subjects assigned to the Class
$subjectsQuery = "
    SELECT s.subject_id, s.subject_name 
    FROM class_subjects cs 
    JOIN subjects s ON cs.subject_id = s.subject_id 
    WHERE cs.class_id = $class_id 
    ORDER BY s.subject_name";
$subjectsResult = $conn->query($subjectsQuery);
$subjects      = $subjectsResult->fetch_all(MYSQLI_ASSOC);

// ***********************
// Build the Table Headers
// ***********************

// Base header columns (S.No, Name, etc.)
$headers = ['S.No', 'Name', 'Family Code', 'Adm.No', 'Cell Number'];

// Add one header per subject
foreach ($subjects as $subject) {
    $headers[] = $subject['subject_name'];
}

// Append summary columns
$headers = array_merge($headers, ['T.Marks', 'Obt.Marks', '%', 'Grade', 'Position']);

// Determine total number of columns and get the last column letter
$totalColumns = count($headers);
$lastColumn   = Coordinate::stringFromColumnIndex($totalColumns);

// ***********************
// Create Spreadsheet & Style Top Rows
// ***********************

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();

// --- School Header (Row 1) ---
$sheet->setCellValue('A1', 'Hazara Public School & College Jamber');
// Merge from A1 to last column on row 1
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
$sheet->setCellValue('A2', 'RESULT SHEET');
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
// You can adjust the merge ranges as needed.
$sheet->setCellValue('A3', "Class/Section: {$classInfo['class_name']} / {$sectionInfo['section_name']}");
$sheet->mergeCells("A3:D3");

$sheet->setCellValue('E3', "Examination: {$examInfo['exam_name']}");
$sheet->mergeCells("E3:H3");

$sheet->setCellValue('I3', "Session: {$sessionInfo['session_name']}");
$sheet->mergeCells("I3:{$lastColumn}3");

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

// Optionally, highlight the percentage, grade, and position headers:
$percentageColIndex = 5 + count($subjects) + 3; // After S.No, Name, Family Code, Adm.No, Cell Number, plus subject columns and two summary columns
$gradeColIndex      = $percentageColIndex + 1;
$positionColIndex   = $gradeColIndex + 1;

$percentageCol = Coordinate::stringFromColumnIndex($percentageColIndex);
$gradeCol      = Coordinate::stringFromColumnIndex($gradeColIndex);
$positionCol   = Coordinate::stringFromColumnIndex($positionColIndex);

$sheet->getStyle("{$percentageCol}5:{$positionCol}5")->applyFromArray([
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFCCE5FF']
    ],
    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// ***********************
// Populate Data Rows (Starting at Row 6)
// ***********************

$rowNumber    = 6;
$studentsData = [];

while ($student = $studentsResult->fetch_assoc()) {
    // Build the row array:
    $rowData = [
        $rowNumber - 5,              // S.No
        $student['student_name'],
        $student['family_code'],     // Family Code
        $student['student_id'],      // Adm.No (assuming student_id as admission no.)
        $student['cell_number']
    ];

    $total_obtained  = 0;
    $total_max       = 0;
    $subjects_graded = 0;

    // Add subject marks (starting from column 6)
    foreach ($subjects as $subject) {
        $marksQuery  = "
            SELECT marks_obtained, max_marks 
            FROM exam_marks 
            WHERE student_id = {$student['student_id']} 
              AND subject_id = {$subject['subject_id']} 
              AND exam_id = $exam_id";
        $marksResult = $conn->query($marksQuery)->fetch_assoc();

        if ($marksResult) {
            $marks_obtained = $marksResult['marks_obtained'];
            $max_marks      = $marksResult['max_marks'];
            $rowData[]      = $marks_obtained;
            $total_obtained += $marks_obtained;
            $total_max      += $max_marks;
            $subjects_graded++;
        } else {
            $rowData[] = ""; // Empty cell for ungraded subject
        }
    }

    // Calculate summary columns
    $percentage = ($subjects_graded > 0 && $total_max > 0) ? ($total_obtained / $total_max) * 100 : 0;
    $grade      = match (true) {
        $percentage >= 90 => 'A+',
        $percentage >= 80 => 'A',
        $percentage >= 70 => 'B',
        $percentage >= 60 => 'C',
        $percentage >= 50 => 'D',
        default           => 'F'
    };

    // Append summary: Total Marks, Obtained Marks, Percentage, Grade
    $rowData[] = $total_max;
    $rowData[] = $total_obtained;
    $rowData[] = number_format($percentage, 2) . '%';
    $rowData[] = $grade;

    // Position will be appended later after sorting
    $studentsData[] = [
        'row'             => $rowNumber,
        'total_obtained'  => $total_obtained,
        'percentage'      => $percentage,
        'grade'           => $grade,
        'data'            => $rowData
    ];

    $rowNumber++;
}

// ***********************
// Sort Students for Positioning (Descending by Total Marks)
// ***********************
usort($studentsData, fn($a, $b) => $b['total_obtained'] <=> $a['total_obtained']);

// ***********************
// Write Student Data and Apply Additional Styling
// ***********************
$position = 1;
foreach ($studentsData as $studentData) {
    // Append Position (e.g., "1st", "2nd", etc.)
    $positionSuffix = ($position === 1) ? "st" : (($position === 2) ? "nd" : (($position === 3) ? "rd" : "th"));
    $studentData['data'][] = "{$position}{$positionSuffix}";

    // Write data into the sheet
    $sheet->fromArray($studentData['data'], null, "A{$studentData['row']}");

    // Optionally, apply alternating row fill colors for better readability:
    $fillColor = ($position % 2 == 0) ? 'FFEFEFEF' : 'FFFFFFFF';
    $sheet->getStyle("A{$studentData['row']}:{$lastColumn}{$studentData['row']}")->applyFromArray([
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['argb' => $fillColor]
        ]
    ]);

    // Style the Percentage cell
    $percentageCell = Coordinate::stringFromColumnIndex($percentageColIndex) . $studentData['row'];
    $sheet->getStyle($percentageCell)->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['argb' => 'FF000080']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    // Color-code the Grade cell
    $gradeCell = Coordinate::stringFromColumnIndex($gradeColIndex) . $studentData['row'];
    $gradeColor = match ($studentData['grade']) {
        'A+' => 'FF00FF00',
        'A'  => 'FF92D050',
        'B'  => 'FFFFFF00',
        'C'  => 'FFFFC000',
        'D'  => 'FFFF0000',
        default => 'FF990000' // For F grade
    };
    $sheet->getStyle($gradeCell)->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['argb' => $gradeColor]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    // Style the Position cell
    $positionCell = Coordinate::stringFromColumnIndex($positionColIndex) . $studentData['row'];
    $sheet->getStyle($positionCell)->applyFromArray([
        'font'      => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFCCE5FF']
        ]
    ]);

    $position++;
}

// ***********************
// Apply Borders to the Entire Table
// ***********************
$sheet->getStyle("A5:{$lastColumn}" . ($rowNumber - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF000000']
        ]
    ]
]);

// ***********************
// Auto-Size All Columns
// ***********************
for ($col = 1; $col <= $totalColumns; $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

// ***********************
// Output the Excel File
// ***********************
if (ob_get_contents()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Class_Report_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit;
