<?php
include 'confiq.php';

if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);

    if ($subject_id > 0) {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
        $stmt->bind_param("i", $subject_id);

        if ($stmt->execute()) {
            header("Location: manage_subjects.php?success=Subject deleted successfully");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid subject ID!";
    }
}
$conn->close();
