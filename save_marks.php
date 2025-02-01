<?php
require_once 'confiq.php';

if ($_POST) {
    $exam_id = intval($_POST['exam_id']);
    $subject_id = intval($_POST['subject_id']);
    $marks_data = $_POST['marks'];

    // Start a transaction for safe insertion
    $conn->begin_transaction();

    try {
        foreach ($marks_data as $student_id => $marks) {
            $student_id = intval($student_id);
            $marks = floatval($marks);

            // Validate marks to ensure they are between 0 and 100
            if ($marks < 0 || $marks > 100) {
                throw new Exception("Marks for student ID $student_id are out of range. Please enter marks between 0 and 100.");
            }

            // Check if marks already exist for this student, exam, and subject
            $checkQuery = $conn->prepare("SELECT mark_id FROM exam_marks WHERE student_id = ? AND exam_id = ? AND subject_id = ?");
            $checkQuery->bind_param("iii", $student_id, $exam_id, $subject_id);
            $checkQuery->execute();
            $result = $checkQuery->get_result();

            if ($result->num_rows > 0) {
                // If marks exist, update them
                $updateQuery = $conn->prepare("UPDATE exam_marks SET marks_obtained = ?, max_marks = 100 WHERE student_id = ? AND exam_id = ? AND subject_id = ?");
                $updateQuery->bind_param("diii", $marks, $student_id, $exam_id, $subject_id);
                $updateQuery->execute();
            } else {
                // If marks do not exist, insert new record
                $insertQuery = $conn->prepare("INSERT INTO exam_marks (student_id, exam_id, subject_id, marks_obtained, max_marks) VALUES (?, ?, ?, ?, 100)");
                $insertQuery->bind_param("iiid", $student_id, $exam_id, $subject_id, $marks);
                $insertQuery->execute();
            }
        }

        // Commit the transaction if all insertions/updates are successful
        $conn->commit();
        header("Location: grade_settings.php?success=Marks Saved Successfully!");
        exit();
    } catch (Exception $e) {
        // Rollback if any error occurs
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No data received!";
}
