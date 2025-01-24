<?php
include 'confiq.php'; // Include database configuration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if student_ids and images are submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_ids'])) {
    $student_ids = $_POST['student_ids'];

    // Loop through all the students to update their images
    foreach ($student_ids as $student_id) {
        // Check if a new image is uploaded
        if (isset($_FILES['image_' . $student_id]) && $_FILES['image_' . $student_id]['error'] == 0) {
            // Handle the file upload
            $image = $_FILES['image_' . $student_id];
            $image_name = $image['name'];
            $image_tmp_name = $image['tmp_name'];
            $image_size = $image['size'];
            $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            // Check if the uploaded image has a valid extension
            if (in_array(strtolower($image_extension), $allowed_extensions)) {
                // Generate a unique name for the image to avoid conflicts
                $new_image_name = 'student_' . $student_id . '.' . $image_extension;
                $upload_path = 'uploads/' . $new_image_name;

                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($image_tmp_name, $upload_path)) {
                    // Update the student's image in the database with the relative path
                    $updateQuery = "UPDATE students SET student_image = ? WHERE student_id = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param('si', $upload_path, $student_id);
                    $stmt->execute();
                } else {
                    echo "<div class='alert alert-danger'>Error uploading image for student ID $student_id.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Invalid image file type for student ID $student_id. Only JPG, JPEG, PNG, and GIF are allowed.</div>";
            }
        }
    }

    // Redirect back using JavaScript
    echo "<script>
            alert('Images updated successfully!');
            window.history.back();
          </script>";
} else {
    echo "<div class='alert alert-danger'>No students selected or no images uploaded.</div>";
}
?>
