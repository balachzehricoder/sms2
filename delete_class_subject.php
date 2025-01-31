<?php
include 'confiq.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $deleteSql = "DELETE FROM class_subjects WHERE class_subject_id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);

    $message = $stmt->execute() ? "Assignment deleted!" : "Error deleting assignment.";

    $stmt->close();
}

$conn->close();
echo "<script>alert('$message'); window.location.href = 'class_subjects.php';</script>";
exit();
