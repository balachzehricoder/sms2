<?php
include 'confiq.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['class_id'])) {
	$classId = $_POST['class_id'];
	$sections = $conn->query("SELECT * FROM sections WHERE class_id = '$classId' ORDER BY section_name ASC");

	echo '<option value="">Select Section</option>';
	while ($section = $sections->fetch_assoc()) {
		echo "<option value='{$section['id']}'>{$section['section_name']}</option>";
	}
}
?>
