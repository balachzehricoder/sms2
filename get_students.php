<?php
require_once 'confiq.php';

$class_id = intval($_GET['class_id']);
$section_id = intval($_GET['section_id']);
$session_id = intval($_GET['session_id']);
$exam_id = intval($_GET['exam_id']);
$subject_id = intval($_GET['subject_id']);

$students = $conn->query("SELECT student_id, student_name FROM students 
                          WHERE class_id = $class_id AND section_id = $section_id AND session = $session_id 
                          ORDER BY student_name");

if ($students->num_rows > 0) {
    echo '<form method="POST" action="save_marks.php">';
    echo '<table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Marks (Out of 100)</th>
                </tr>
            </thead>
            <tbody>';
    while ($student = $students->fetch_assoc()) {
        echo "<tr>
                <td>{$student['student_name']}</td>
                <td><input type='number' class='form-control w-25' name='marks[{$student['student_id']}]' min='0' max='100' required></td>
              </tr>";
    }
    echo '</tbody></table>';
    echo "<input type='hidden' name='class_id' value='$class_id'>
          <input type='hidden' name='section_id' value='$section_id'>
          <input type='hidden' name='session_id' value='$session_id'>
          <input type='hidden' name='exam_id' value='$exam_id'>
          <input type='hidden' name='subject_id' value='$subject_id'>
          <button type='submit' class='btn btn-success'>Save Marks</button>
          </form>";
} else {
    echo "<p>No students found for the selected criteria.</p>";
}
