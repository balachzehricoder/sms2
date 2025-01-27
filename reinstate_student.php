<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    $updateQuery = "UPDATE students SET status = 'active' WHERE student_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        header("Location: struck_off?message=Reinstated successfully");
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Invalid request.";
}
