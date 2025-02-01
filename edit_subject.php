<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = intval($_POST['subject_id']);
    $subject_name = trim($_POST['subject_name']);

    if (!empty($subject_name) && $subject_id > 0) {
        $stmt = $conn->prepare("UPDATE subjects SET subject_name = ? WHERE subject_id = ?");
        $stmt->bind_param("si", $subject_name, $subject_id);

        if ($stmt->execute()) {
            header("Location: manage_subjects.php?success=Subject updated successfully");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Invalid input!";
    }
}
$conn->close();
