<?php
include 'confiq.php'; // Include database configuration
// include 'header.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch students data based on class, section, and session filters
$class_id = isset($_GET['class']) ? $_GET['class'] : '';
$section_id = isset($_GET['section']) ? $_GET['section'] : '';
$session_id = isset($_GET['session']) ? $_GET['session'] : '';

// Debugging input data
echo "<pre>Debugging Input Data:\n";
echo "Class ID: $class_id\n";
echo "Section ID: $section_id\n";
echo "Session ID: $session_id\n</pre>";

$query = "SELECT 
            students.student_id,
            students.student_name,
            students.father_name,
            students.family_code,
            classes.class_name,
            sections.section_name,
            sessions.session_name,
            students.gr_no
          FROM students
          LEFT JOIN classes ON students.class_id = classes.class_id
          LEFT JOIN sections ON students.section_id = sections.section_id
          LEFT JOIN sessions ON students.session = sessions.id
          WHERE 1=1";

// Apply filters
if (!empty($class_id)) {
    $query .= " AND students.class_id = '$class_id'";
}
if (!empty($section_id)) {
    $query .= " AND students.section_id = '$section_id'";
}
if (!empty($session_id)) {
    $query .= " AND students.session = '$session_id'";
}

// Debugging: Output final query
echo "<pre>Generated SQL Query:\n$query\n</pre>";

// Execute the query
$result = $conn->query($query);

// Debugging: Check for query errors
if (!$result) {
    die("<pre>SQL Error: " . $conn->error . "</pre>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Cards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .id-card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            page-break-inside: avoid;
        }
        .id-card {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            page-break-inside: avoid;
        }
        .id-card h3 {
            margin: 0;
            font-size: 14px;
            color: red;
        }
        .id-card p {
            margin: 5px 0;
        }
        .id-card strong {
            display: block;
        }
        .id-card img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }
        .print-btn {
            margin: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">Print ID Cards</button>
    </div>
    <div class="id-card-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="id-card">
                    <h3>Hazara Public School & College Jamber</h3>
                    <p><strong>Class/Sec:</strong> <?= $row['class_name'] ?> / <?= $row['section_name'] ?></p>
                    <p><strong>Adm no:</strong> <?= $row['gr_no'] ?></p>
                    <p><strong>Student Name:</strong> <?= $row['student_name'] ?></p>
                    <p><strong>Father Name:</strong> <?= $row['father_name'] ?></p>
                    <p><strong>Family Code:</strong> <?= $row['family_code'] ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No students found for the selected filters.</p>
        <?php endif; ?>
    </div>
</body>
</html>
