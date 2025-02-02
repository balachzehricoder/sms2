<?php
// edit_criterion.php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';
error_reporting(E_ALL);

// Ensure an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('No criterion ID provided');window.location.href='performance_heads.php';</script>";
    exit;
}

$criterion_id = intval($_GET['id']);

// Fetch the criterion record
$sql = "SELECT * FROM performance_criteria WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $criterion_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Criterion not found');window.location.href='performance_heads.php';</script>";
    exit;
}
$criterion = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle form submission for updating the criterion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_criterion'])) {
    $criterion_name = trim($_POST['criterion_name']);
    $description    = trim($_POST['description']);

    if (!empty($criterion_name)) {
        $update_sql = "UPDATE performance_criteria SET criteria_name = ?, description = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssi", $criterion_name, $description, $criterion_id);
        if (mysqli_stmt_execute($update_stmt)) {
            echo "<script>alert('Criterion updated successfully.');window.location.href='performance_heads.php';</script>";
            exit;
        } else {
            $message = "Error updating criterion: " . mysqli_error($conn);
        }
        mysqli_stmt_close($update_stmt);
    } else {
        $message = "Please fill in the required fields.";
    }
}
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="container-fluid">
        <h2>Edit Performance Criterion</h2>
        <?php if (isset($message)) {
            echo "<div class='alert alert-info'>" . htmlspecialchars($message) . "</div>";
        } ?>
        <form method="POST" action="">
            <input type="hidden" name="update_criterion" value="1">
            <div class="form-group">
                <label for="criterion_name">Criterion Name</label>
                <input type="text" name="criterion_name" id="criterion_name" class="form-control" value="<?php echo htmlspecialchars($criterion['criteria_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($criterion['description']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Criterion</button>
        </form>
    </div>
</div>
<!-- END MAIN CONTENT -->

<?php include 'footer.php'; ?>