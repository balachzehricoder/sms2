<?php
include 'confiq.php'; // Include your database configuration file

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];

    // Delete the class from the database
    $query = "DELETE FROM classes WHERE class_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $class_id);

    if ($stmt->execute()) {
        header("Location: post?message=Class deleted successfully");
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
