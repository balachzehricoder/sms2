<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $teacher_id = intval($_POST['teacher_id']);
    $class_id = intval($_POST['class_id']);
    $subject_id = intval($_POST['subject_id']);

    // Prevent duplicate assignments
    $checkSql = "SELECT id FROM teacher_subjects WHERE teacher_id = ? AND class_id = ? AND subject_id = ? AND id != ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("iiii", $teacher_id, $class_id, $subject_id, $id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $message = "This teacher is already assigned to this subject in this class.";
    } else {
        $updateSql = "UPDATE teacher_subjects SET teacher_id = ?, class_id = ?, subject_id = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateSql);
        $stmtUpdate->bind_param("iiii", $teacher_id, $class_id, $subject_id, $id);
        $stmtUpdate->execute();
        $message = "Assignment updated successfully!";
    }

    $stmtCheck->close();
    $conn->close();
    echo "<script>alert('$message'); window.location.href = 'teacher_subjects.php';</script>";
    exit();
}
