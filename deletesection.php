<?php
include 'confiq.php';

if (isset($_GET['section_id'])) {
    $section_id = $_GET['section_id'];

    $deleteQuery = "DELETE FROM sections WHERE section_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $section_id);

    if ($stmt->execute()) {
        header("Location: section?delete_success=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
