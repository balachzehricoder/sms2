<?php
include 'confiq.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $standard_monthly_fee = isset($_POST['standard_monthly_fee']) ? floatval($_POST['standard_monthly_fee']) : 0;

    if ($class_id > 0 && $standard_monthly_fee >= 0) {
        $query = "UPDATE classes SET standard_monthly_fee = ? WHERE class_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('di', $standard_monthly_fee, $class_id);

        if ($stmt->execute()) {
            echo "<script>alert('Fee updated successfully!'); window.location.href='classwise';</script>";
        } else {
            echo "<script>alert('Error updating fee.'); window.location.href='classwise';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Invalid data provided.'); window.location.href='classwise';</script>";
    }
}

$conn->close();
?>
