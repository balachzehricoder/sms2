<?php
include 'confiq.php'; // Database connection
include 'header.php'; // Optional: Add header
include 'sidebar.php'; // Optional: Add sidebar

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and assign the POST variables to PHP variables
    $session = mysqli_real_escape_string($conn, $_POST['session']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);
    $challan_month = mysqli_real_escape_string($conn, $_POST['challan_month']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $fee_type = mysqli_real_escape_string($conn, $_POST['fee_type']);

    // Fetch the fee amount from the classes table based on the selected class_id
    $fee_query = "SELECT standard_monthly_fee FROM classes WHERE class_id = '$class_id'";
    $fee_result = $conn->query($fee_query);

    if ($fee_result->num_rows > 0) {
        // Fetch fee amount
        $fee_row = $fee_result->fetch_assoc();
        $fee_amount = $fee_row['standard_monthly_fee'];
    } else {
        // If class doesn't have a fee amount, set a default value
        $fee_amount = 0;
    }

    // Prepare the SQL query to insert the challan data including the fee amount
    $sql = "INSERT INTO challans (session, class_id, section_id, challan_month, due_date, fee_type, fee_amount) 
            VALUES ('$session', '$class_id', '$section_id', '$challan_month', '$due_date', '$fee_type', '$fee_amount')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        // Redirect to the page that shows all challans or a success message
        echo "<script>alert('Challan created successfully!'); window.location.href='viewchallan.php';</script>";
    } else {
        // Error message if the query fails
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close(); // Close the database connection
include 'footer.php'; // Optional: Add footer
?>
