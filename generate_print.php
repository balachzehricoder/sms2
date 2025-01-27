<?php
require 'vendor/autoload.php'; // Include Dompdf

use Dompdf\Dompdf;

// Database connection
include 'confiq.php';

// Get selected fields and sorting preferences
$fields = $_GET['fields'] ?? ['student_id', 'family_code', 'student_name']; // Default fields
$sortBy = $_GET['sort_by'] ?? 'date_of_admission'; // Default sorting

// Validate fields
$validFields = ['student_id', 'family_code', 'student_name', 'gender', 'class_id', 'section_id', 'date_of_admission', 'status'];
$selectedFields = array_intersect($fields, $validFields);

// If no valid fields are selected, set a default
if (empty($selectedFields)) {
    $selectedFields = ['student_id', 'student_name'];
}

// Build the query dynamically
$fieldList = implode(', ', $selectedFields);
$studentQuery = "SELECT $fieldList FROM students ORDER BY $sortBy ASC";
$studentResult = $conn->query($studentQuery);

if (!$studentResult) {
    die("Query failed: " . $conn->error);
}

// Start building HTML for the PDF
$html = '<h2 style="text-align: center;">Students Report</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="5" style="width: 100%; border-collapse: collapse; text-align: center;">';

// Add table headers
$html .= '<thead><tr>';
foreach ($selectedFields as $field) {
    $html .= '<th>' . ucfirst(str_replace('_', ' ', $field)) . '</th>';
}
$html .= '</tr></thead>';

// Add table rows
$html .= '<tbody>';
if ($studentResult->num_rows > 0) {
    while ($row = $studentResult->fetch_assoc()) {
        $html .= '<tr>';
        foreach ($selectedFields as $field) {
            $html .= '<td>' . htmlspecialchars($row[$field]) . '</td>';
        }
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="' . count($selectedFields) . '">No data available</td></tr>';
}
$html .= '</tbody></table>';

// Initialize Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Landscape orientation, A4 size
$dompdf->render();

// Stream the PDF to the browser
$dompdf->stream('students_report_' . date('Y-m-d') . '.pdf', ['Attachment' => false]);

// Close the database connection
$conn->close();
exit;
