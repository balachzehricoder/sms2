<?php
include 'confiq.php'; // Include your database configuration file
include 'header.php';
include 'sidebar.php';
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];

    // Fetch class details to edit
    $query = "SELECT * FROM classes WHERE class_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $class = $result->fetch_assoc();

    if (!$class) {
        die("Class not found");
    }
}



include 'confiq.php'; // Include your database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $standard_monthly_fee = $_POST['standard_monthly_fee'];

    // Update the class in the database
    $query = "UPDATE classes SET standard_monthly_fee = ? WHERE class_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $standard_monthly_fee, $class_name);

    if ($stmt->execute()) {
        header("Location: post?message=Class updated successfully");
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
    <div class="container-fluid">
    <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="modal-body">
        <div class="basic-form">
            <div class="container p-4 border rounded bg-light shadow">
                <h2 class="text-center mb-4">Edit Class</h2>
                <form action="" method="post">
                    <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>">
                    <div class="form-group mb-3">
                        <label for="standard-monthly-fee">Standard Monthly Fee</label>
                        <input type="number" class="form-control" id="standard-monthly-fee" name="standard_monthly_fee" value="<?php echo htmlspecialchars($class['standard_monthly_fee']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Class</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php  include 'footer.php';
?>