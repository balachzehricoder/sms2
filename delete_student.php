<?php
include 'confiq.php'; // Include your database configuration file

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Delete the student record from the database
    $query = "DELETE FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($query);

    // Bind the parameter as an integer
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        // Redirect to the student page with a success message
        header("Location: student?message=Student deleted successfully");
        exit();
    } else {
        // If there's an error, show the error message
        echo "Error: " . $conn->error;
    }

    // Close the prepared statement and the database connection
    $stmt->close();
    $conn->close();
} else {
    echo "No student ID provided.";
}
?>
