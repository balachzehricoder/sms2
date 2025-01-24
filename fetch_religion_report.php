<?php
include 'confiq.php';

$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';
$session = $_GET['session'] ?? '';
$religion = $_GET['religion'] ?? '';

$query = "SELECT student_id, student_name, class_id, section_id, religion, dob, gender, father_name FROM students WHERE 1=1";

if ($class)
	$query .= " AND class_id = '$class'";
if ($section)
	$query .= " AND section_id = '$section'";

if ($religion)
	$query .= " AND religion = '$religion'";

$query .= " ORDER BY student_name ASC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
	echo '<table class="table table-striped table-bordered">';
	echo '<thead><tr><th>Student ID</th><th>Student Name</th><th>Class</th><th>Section</th><th>Religion</th><th>Date of Birth</th><th>Gender</th><th>Father Name</th><th>Actions</th></tr></thead><tbody>';
	while ($row = $result->fetch_assoc()) {
		echo "<tr>
                <td>{$row['student_id']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['class_id']}</td>
                <td>{$row['section_id']}</td>
                <td>{$row['religion']}</td>
                <td>{$row['dob']}</td>
                <td>{$row['gender']}</td>
                <td>{$row['father_name']}</td>
                <td>
                    <a href='edit_student.php?id={$row['student_id']}' class='btn btn-sm btn-warning'>Edit</a>
                    <a href='delete_student.php?id={$row['student_id']}' class='btn btn-sm btn-danger'>Delete</a>
                </td>
              </tr>";
	}
	echo '</tbody></table>';
} else {
	echo '<p>No students found for the selected criteria.</p>';
}
?>
