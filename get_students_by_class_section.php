<?php
require_once 'confiq.php';

$class_id = intval($_GET['class_id']);
$section_id = intval($_GET['section_id']);

$studentsQuery = "SELECT student_id, student_name FROM students WHERE class_id = $class_id AND section_id = $section_id ORDER BY student_name";
$studentsResult = $conn->query($studentsQuery);

echo '<option value="">Select Student</option>';
if ($studentsResult->num_rows > 0) {
    while ($student = $studentsResult->fetch_assoc()) {
        echo "<option value='{$student['student_id']}'>{$student['student_name']}</option>";
    }
} else {
    echo '<option value="">No students found</option>';
}
