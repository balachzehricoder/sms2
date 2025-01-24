<?php
include 'confiq.php'; // Include your database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $standard_monthly_fee = $_POST['standard_monthly_fee'];

    // Insert new class into the database
    $query = "INSERT INTO classes (class_name, standard_monthly_fee, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $class_name, $standard_monthly_fee);

    if ($stmt->execute()) {
        header("Location: post?message=Class added successfully");
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>
