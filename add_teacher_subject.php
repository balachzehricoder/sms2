<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $class_id = intval($_POST['class_id']);
    $subject_id = intval($_POST['subject_id']);

    // Prevent duplicate assignments
    $checkSql = "SELECT id FROM teacher_subjects WHERE teacher_id = ? AND class_id = ? AND subject_id = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("iii", $teacher_id, $class_id, $subject_id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $message = "This teacher is already assigned to this subject in this class.";
    } else {
        // Insert the assignment
        $insertSql = "INSERT INTO teacher_subjects (teacher_id, class_id, subject_id) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("iii", $teacher_id, $class_id, $subject_id);
        $stmtInsert->execute();
        $message = "Teacher assigned successfully!";
    }

    $stmtCheck->close();
    $conn->close();
    echo "<script>alert('$message'); window.location.href = 'teacher_subjects.php';</script>";
    exit();
}
