<?php
// delete_exam.php

include 'confiq.php';  // Database connection

// 1. Check if exam_id is set and is a valid integer
if (isset($_GET['exam_id']) && is_numeric($_GET['exam_id'])) {
    $exam_id = intval($_GET['exam_id']);

    // 2. Check if the exam exists before attempting to delete
    $checkExam = "SELECT exam_id FROM exams WHERE exam_id = ?";
    $stmtCheck = $conn->prepare($checkExam);
    $stmtCheck->bind_param("i", $exam_id);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        // 3. Proceed to delete the exam
        $deleteExam = "DELETE FROM exams WHERE exam_id = ?";
        $stmtDelete = $conn->prepare($deleteExam);
        $stmtDelete->bind_param("i", $exam_id);

        if ($stmtDelete->execute()) {
            $message = "Exam deleted successfully!";
        } else {
            $message = "Error deleting exam.";
        }
        $stmtDelete->close();
    } else {
        $message = "Exam not found.";
    }
    $stmtCheck->close();
} else {
    $message = "Invalid request.";
}

// 4. Redirect back to exams.php with message
$conn->close();
echo "<script>
    alert('$message');
    window.location.href = 'exams.php';
</script>";
exit();
