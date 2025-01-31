<?php
// update_exam.php

include 'confiq.php';  // MySQLi connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collect data from the form
    $exam_id     = isset($_POST['exam_id'])     ? intval($_POST['exam_id']) : 0;
    $exam_name   = isset($_POST['exam_name'])   ? trim($_POST['exam_name']) : '';
    $exam_type   = isset($_POST['exam_type_id']) ? intval($_POST['exam_type_id']) : 0;
    $start_date  = isset($_POST['start_date'])  ? $_POST['start_date']      : null;
    $end_date    = isset($_POST['end_date'])    ? $_POST['end_date']        : null;
    $session_id  = (isset($_POST['session_id']) && $_POST['session_id'] !== '')
        ? intval($_POST['session_id'])
        : null;

    // 2. Basic validation
    if ($exam_id > 0 && !empty($exam_name) && $exam_type > 0) {
        // 3. Prepare the UPDATE query
        $sql = "UPDATE exams
                SET exam_type_id = ?,
                    exam_name    = ?,
                    start_date   = ?,
                    end_date     = ?,
                    session_id   = ?
                WHERE exam_id    = ?";
        $stmt = mysqli_prepare($conn, $sql);
        // i s s s i i => exam_type, exam_name, start_date, end_date, session, exam_id
        mysqli_stmt_bind_param(
            $stmt,
            "isssii",
            $exam_type,
            $exam_name,
            $start_date,
            $end_date,
            $session_id,
            $exam_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $rowsAffected = mysqli_stmt_affected_rows($stmt);
            if ($rowsAffected > 0) {
                $message = "Exam updated successfully!";
            } else {
                $message = "No changes made or invalid Exam ID.";
            }
        } else {
            $message = "Error updating exam.";
        }
        mysqli_stmt_close($stmt);
    } else {
        // If validation fails
        $message = "Please fill in all required fields.";
    }
} else {
    // If accessed directly or not POST
    $message = "Invalid request.";
}

// 4. Redirect back to exams.php
$conn->close();
echo "<script>
    alert('$message');
    window.location.href = 'exams.php';
</script>";
exit;
