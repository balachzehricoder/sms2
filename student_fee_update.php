<?php
include 'confiq.php'; // Include database configuration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['monthly_fee'])) {
    $monthlyFees = $_POST['monthly_fee'];

    // Prepare update query
    $updateQuery = "UPDATE students SET monthly_fee = ? WHERE student_id = ?";
    $stmt = $conn->prepare($updateQuery);

    // Counter for success and failure
    $updatedCount = 0;
    $failedCount = 0;

    foreach ($monthlyFees as $studentId => $fee) {
        if (is_numeric($fee)) { // Validate fee input
            $stmt->bind_param('di', $fee, $studentId);
            if ($stmt->execute()) {
                $updatedCount++;
            } else {
                $failedCount++;
            }
        } else {
            $failedCount++;
        }
    }

    $stmt->close();

    // Redirect back with a success or error message
    header("Location: view_class?class_id=" . $_GET['class_id'] . "&updated=$updatedCount&failed=$failedCount");
    exit;
} else {
    // Redirect to class view if accessed directly
    header("Location: view_class?class_id=" . $_GET['class_id'] . "&error=invalid_request");
    exit;
}
