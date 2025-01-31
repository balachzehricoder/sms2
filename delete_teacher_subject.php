<?php
include 'confiq.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $deleteSql = "DELETE FROM teacher_subjects WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Assignment deleted successfully!";
    } else {
        $message = "Error deleting assignment.";
    }

    $conn->close();
    echo "<script>alert('$message'); window.location.href = 'teacher_subjects.php';</script>";
    exit();
}
