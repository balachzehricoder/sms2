<?php
include 'confiq.php'; // Include database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$family_code = $_POST['family_code'] ?? null;
	$student_name = $_POST['student_name'];
	$session = $_POST['session'];
	$class_name = $_POST['class_name'];
	$section_name = $_POST['section_name'];
	$gender = $_POST['gender'];
	$religion = $_POST['religion'];
	$dob = $_POST['dob'];
	$date_of_admission = $_POST['date_of_admission'];
	$whatsapp_number = $_POST['whatsapp_number'];
	$father_cell_no = $_POST['father_cell_no'];
	$mother_cell_no = $_POST['mother_cell_no'];
	$home_cell_no = $_POST['home_cell_no'];
	$place_of_birth = $_POST['place_of_birth'];
	$state = $_POST['state'];
	$city = $_POST['city'];
	$email = $_POST['email'];
	$father_name = $_POST['father_name'];
	$mother_name = $_POST['mother_name'];
	$home_address = $_POST['home_address'];

	// Auto-generate family_code if not provided
	if (empty($family_code)) {
		$result = $conn->query("SELECT MAX(family_code) as max_code FROM students");
		$row = $result->fetch_assoc();
		$max_code = $row['max_code'];
		$family_code = $max_code ? intval($max_code) + 1 : 1; // Increment or start from 1
	}

	// Handle the file upload
	$target_dir = "uploads/";
	$target_file = $target_dir . basename($_FILES["student_image"]["name"]);
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

	// Check if image file is an actual image
	$check = getimagesize($_FILES["student_image"]["tmp_name"]);
	if ($check !== false) {
		if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $target_file)) {
			// Prepare the insert statement
			$stmt = $conn->prepare("
                INSERT INTO students
                (family_code, student_name, session, class_id, section_id, gender, religion, dob, date_of_admission,
                 whatsapp_number, father_cell_no, mother_cell_no, home_cell_no, place_of_birth, state, city, email,
                 father_name, mother_name, home_address, student_image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
			$stmt->bind_param(
			"sssssssssssssssssssss",
			$family_code, $student_name, $session, $class_name, $section_name, $gender, $religion, $dob,
			$date_of_admission, $whatsapp_number, $father_cell_no, $mother_cell_no, $home_cell_no, $place_of_birth,
			$state, $city, $email, $father_name, $mother_name, $home_address, $target_file
			);

			if ($stmt->execute()) {
				
				header("Location: student");

			} else {
				echo "Error: " . $stmt->error;
			}
			$stmt->close();
		} else {
			echo "Sorry, there was an error uploading your file.";
		}
	} else {
		echo "File is not an image.";
	}

	$conn->close();
}
?>
