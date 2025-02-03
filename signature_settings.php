<?php
// signature_settings.php
include 'confiq.php';    // Database connection (MySQLi)
include 'header.php';    // Contains layout header, <body>, etc.
include 'sidebar.php';   // Sidebar navigation
error_reporting(E_ALL);

// ---------------------------------------------------------
// Handle Form Submission for Uploading Signatures
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_signature'])) {

    $examHeadPath = "";
    $principalPath = "";

    // Handle Exam Head Signature Upload
    if (!empty($_FILES['exam_head_signature']['name'])) {
        $examHeadPath = 'uploads/' . time() . '_exam_head.png';
        move_uploaded_file($_FILES['exam_head_signature']['tmp_name'], $examHeadPath);
    }

    // Handle Principal Signature Upload
    if (!empty($_FILES['principal_signature']['name'])) {
        $principalPath = 'uploads/' . time() . '_principal.png';
        move_uploaded_file($_FILES['principal_signature']['tmp_name'], $principalPath);
    }

    if ($examHeadPath && $principalPath) {
        $query = "INSERT INTO signatures (exam_head_signature, principal_signature) VALUES ('$examHeadPath', '$principalPath')";
        mysqli_query($conn, $query);
        $message = "Signatures uploaded successfully!";
    } else {
        $message = "Error uploading signatures.";
    }
}

// ---------------------------------------------------------
// Fetch Existing Signatures (most recent first)
$signaturesQuery = "SELECT * FROM signatures ORDER BY id DESC";
$resultSignatures = $conn->query($signaturesQuery);
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <!-- Breadcrumb / Page Titles -->
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Signature Settings</a></li>
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
                <!-- Card for the Signatures Table -->
                <div class="card">
                    <div class="card-body">
                        <!-- Header section with "Add New Signature" button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Uploaded Signatures</h4>
                            <button type="button" class="btn btn-success btn-rounded" data-toggle="modal" data-target="#add-new-signature">
                                <i class="fa fa-upload"></i> Upload New Signatures
                            </button>
                        </div>

                        <!-- Signature Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Exam Head Signature</th>
                                        <th>Principal Signature</th>
                                        <th>Uploaded On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultSignatures && $resultSignatures->num_rows > 0): ?>
                                        <?php while ($row = $resultSignatures->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><img src="<?php echo htmlspecialchars($row['exam_head_signature']); ?>" height="60"></td>
                                                <td><img src="<?php echo htmlspecialchars($row['principal_signature']); ?>" height="60"></td>
                                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                <td>
                                                    <a href="delete_signature.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this signature?');">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5">No signatures uploaded yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- End Signature Table -->
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    <!-- End Container -->
</div>
<!-- END MAIN CONTENT -->

<!-- MODAL: Upload New Signatures -->
<div class="modal fade" id="add-new-signature">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload New Signatures</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="basic-form">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="upload_signature" value="1">

                        <!-- Exam Head Signature -->
                        <div class="form-group">
                            <label for="exam_head_signature">Exam Head Signature</label>
                            <input type="file" class="form-control" id="exam_head_signature" name="exam_head_signature" accept="image/*" required>
                        </div>

                        <!-- Principal Signature -->
                        <div class="form-group">
                            <label for="principal_signature">Principal Signature</label>
                            <input type="file" class="form-control" id="principal_signature" name="principal_signature" accept="image/*" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Upload Signatures</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>