<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id']);
    $subject_id = intval($_POST['subject_id']);

    // Prevent duplicate entries
    $checkSql = "SELECT class_subject_id FROM class_subjects WHERE class_id = ? AND subject_id = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ii", $class_id, $subject_id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $message = "This subject is already assigned to the class.";
    } else {
        $insertSql = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("ii", $class_id, $subject_id);

        if ($stmtInsert->execute()) {
            $message = "Subject assigned successfully!";
        } else {
            $message = "Error assigning subject.";
        }
        $stmtInsert->close();
    }
    $stmtCheck->close();
}

$conn->close();
echo "<script>alert('$message'); window.location.href = 'class_subjects.php';</script>";
exit();
