<?php
require_once 'confiq.php';

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id) {
    $subjects = $conn->query("SELECT s.subject_id, s.subject_name FROM class_subjects cs 
                              JOIN subjects s ON cs.subject_id = s.subject_id 
                              WHERE cs.class_id = $class_id ORDER BY s.subject_name");

    echo '<option value="">Select Subject</option>';
    while ($subject = $subjects->fetch_assoc()) {
        echo "<option value='{$subject['subject_id']}'>{$subject['subject_name']}</option>";
    }
}
