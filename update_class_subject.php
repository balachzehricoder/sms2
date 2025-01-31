<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id']);
    $selected_subjects = isset($_POST['subject_ids']) ? $_POST['subject_ids'] : [];

    // Remove old subjects not in the selected list
    if (!empty($selected_subjects)) {
        $placeholders = implode(',', array_fill(0, count($selected_subjects), '?'));
        $types = str_repeat('i', count($selected_subjects));

        $deleteSql = "DELETE FROM class_subjects WHERE class_id = ? AND subject_id NOT IN ($placeholders)";
        $stmtDelete = $conn->prepare($deleteSql);
        $stmtDelete->bind_param("i" . $types, $class_id, ...$selected_subjects);
        $stmtDelete->execute();
    } else {
        // If no subjects selected, remove all assigned subjects
        $deleteSql = "DELETE FROM class_subjects WHERE class_id = ?";
        $stmtDelete = $conn->prepare($deleteSql);
        $stmtDelete->bind_param("i", $class_id);
        $stmtDelete->execute();
    }

    // Insert new subjects (only if not already assigned)
    foreach ($selected_subjects as $subject_id) {
        $checkSql = "SELECT class_subject_id FROM class_subjects WHERE class_id = ? AND subject_id = ?";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->bind_param("ii", $class_id, $subject_id);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows == 0) {
            $insertSql = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("ii", $class_id, $subject_id);
            $stmtInsert->execute();
            $stmtInsert->close();
        }
        $stmtCheck->close();
    }

    $conn->close();
    echo "<script>alert('Subjects updated successfully!'); window.location.href = 'class_subjects.php';</script>";
    exit();
}
