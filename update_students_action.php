<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student Info</title>
    <!-- Link to external CSS file -->
    <link href="css/style.css" rel="stylesheet">
    <link href="./css/custome.css" rel="stylesheet">
    <link href="./plugins/tables/css/datatable/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>
<body>
<?php
include 'confiq.php'; // Include database configuration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get class ID
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

    if ($class_id === 0) {
        echo "<div class='alert alert-danger'>Invalid Class ID.</div>";
        exit;
    }

    // Loop through the student data
    foreach ($_POST['student_id'] as $student_id => $student_id_value) {
        // Collect each value for the current student
        $family_code = $_POST['family_code'][$student_id] ?? '';
        $student_name = $_POST['student_name'][$student_id] ?? '';
        $gender = $_POST['gender'][$student_id] ?? '';
        $father_name = $_POST['father_name'][$student_id] ?? '';
        $father_cell_no = $_POST['father_cell_no'][$student_id] ?? '';
        $dob = $_POST['dob'][$student_id] ?? '';
        $date_of_admission = $_POST['date_of_admission'][$student_id] ?? '';
        $session = $_POST['session'][$student_id] ?? '';
        $religion = $_POST['religion'][$student_id] ?? '';
        $monthly_fee = $_POST['monthly_fee'][$student_id] ?? 0;

        // Construct the query with placeholders
        $updateQuery = "UPDATE students 
                        SET 
                            family_code = ?, 
                            student_name = ?, 
                            gender = ?, 
                            father_name = ?, 
                            father_cell_no = ?, 
                            dob = ?, 
                            date_of_admission = ?, 
                            session = ?, 
                            religion = ?, 
                            monthly_fee = ?
                        WHERE student_id = ? AND class_id = ?";

        // Prepare the statement
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            echo "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
            continue;
        }

        // Bind the parameters
        $stmt->bind_param(
            'sssssssdsiis', // Bind types, notice the 'd' for decimals, 'i' for integers, 's' for strings
            $family_code,
            $student_name,
            $gender,
            $father_name,
            $father_cell_no,
            $dob,
            $date_of_admission,
            $session,
            $religion,
            $monthly_fee,
            $student_id,
            $class_id
        );

        // Execute the statement
        if (!$stmt->execute()) {
            echo "<div class='alert alert-danger'>Error updating Student ID $student_id: " . $stmt->error . "</div>";
        }
    }

    echo "<div class='alert alert-success'>Student information updated successfully!</div>";
    echo "<a href='update_students?class_id=$class_id' class='btn btn-primary'>Go Back</a>";
} else {
    echo "<div class='alert alert-danger'>Invalid request method.</div>";
}
?>
