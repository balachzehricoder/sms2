<?php
// performance_heads.php
include 'confiq.php';    // Database connection (MySQLi)
include 'header.php';    // Contains layout header, <body>, etc.
include 'sidebar.php';   // Sidebar navigation
error_reporting(E_ALL);

// ---------------------------------------------------------
// 1. Handle Form Submission for Creating a New Performance Criterion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_criterion'])) {

    // Retrieve and sanitize form fields
    $criterion_name = trim($_POST['criterion_name']);
    $description    = trim($_POST['description']);

    if (!empty($criterion_name)) {
        // Insert into performance_criteria table using a prepared statement
        $sql = "INSERT INTO performance_criteria (criteria_name, description) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $criterion_name, $description);

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            $message = "Performance criterion created successfully!";
        } else {
            $message = "Error inserting criterion: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Please fill in the required fields.";
    }
}

// ---------------------------------------------------------
// 2. Fetch Existing Performance Criteria (most recent first)
$criteriaQuery = "SELECT * FROM performance_criteria ORDER BY id DESC";
$resultCriteria = $conn->query($criteriaQuery);
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <!-- Breadcrumb / Page Titles -->
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Performance Criteria</a></li>
            </ol>
        </div>
    </div>

    <!-- Container -->
    <div class="container-fluid">
        <?php
        // Display any success/error message if set
        if (isset($message)) {
            echo "<div class='alert alert-info'>" . htmlspecialchars($message) . "</div>";
        }
        ?>
        <div class="row">
            <div class="col-12">
                <!-- Card for the Performance Criteria table -->
                <div class="card">
                    <div class="card-body">
                        <!-- Header section with "Add New Criterion" button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">All Performance Criteria</h4>
                            <div>
                                <button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-criterion">
                                    <i class="fa fa-plus-circle"></i> Add New Criterion
                                </button>
                            </div>
                        </div>

                        <!-- Performance Criteria Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Criterion Name</th>
                                        <th>Description</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultCriteria && $resultCriteria->num_rows > 0): ?>
                                        <?php while ($row = $resultCriteria->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['criteria_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                <td>
                                                    <a href="edit_criterion.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-primary">Edit</a>
                                                    <a href="delete_criterion.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this criterion?');">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5">No performance criteria found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- End Criteria Table -->
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    <!-- End Container -->
</div>
<!-- END MAIN CONTENT -->

<!-- MODAL: Add New Performance Criterion -->
<div class="modal fade" id="add-new-criterion">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Performance Criterion</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="basic-form">
                    <!-- The form posts to the same page -->
                    <form action="" method="POST">
                        <input type="hidden" name="create_criterion" value="1">
                        <!-- Criterion Name -->
                        <div class="form-group">
                            <label for="criterion_name">Criterion Name</label>
                            <input type="text" class="form-control" id="criterion_name" name="criterion_name" required>
                        </div>
                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description (optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Add Criterion</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>