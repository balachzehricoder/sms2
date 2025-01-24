<?php
include 'confiq.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_name = $_POST['section_name'];
    $class_id = $_POST['class_id'];

    $sql = "INSERT INTO sections (section_name, class_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $section_name, $class_id);

    if ($stmt->execute()) {
        header("Location: section?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
