<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = trim($_POST['subject_name']);

    if (!empty($subject_name)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
        $stmt->bind_param("s", $subject_name);

        if ($stmt->execute()) {
            header("Location: manage_subjects.php?success=Subject added successfully");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Subject name cannot be empty!";
    }
}
$conn->close();
