<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';

// Fetch classes for the dropdown


// Fetch sections to display
$sectionQuery = "SELECT * from sections ";
$sectionResult = $conn->query($sectionQuery);
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Sections</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">All Sections</h4>
                            <button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-section">
                                <i class="fa fa-plus-circle"></i> Add New Section
                            </button>
                        </div>

                        <!-- Sections Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>Section ID</th>
                                        <th>Section Name</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($sectionResult->num_rows > 0) {
                                        while ($sectionRow = $sectionResult->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>" . htmlspecialchars($sectionRow['section_id']) . "</td>
                                                    <td>" . htmlspecialchars($sectionRow['section_name']) . "</td>
                                                    <td>" . htmlspecialchars($sectionRow['created_at']) . "</td>
                                                   
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5'>No sections found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add New Section Modal -->
<div class="modal fade" id="add-new-section">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Section</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="basic-form">
                    <form action="addsection.php" method="post">
                        <div class="form-group">
                            <label for="section-name">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="section-name" name="section_name" placeholder="Enter section name..." required>
                        </div>
                        <div class="form-group">
                            <label for="class-id">Class Name <span class="text-danger">*</span></label>
                            <select class="form-control" id="class-id" name="class_id" required>
                                <option value="" disabled selected>Select a Class</option>
                                <?php
                                if ($classResult->num_rows > 0) {
                                    while ($classRow = $classResult->fetch_assoc()) {
                                        echo "<option value='" . $classRow['class_id'] . "'>" . htmlspecialchars($classRow['class_name']) . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No Classes Available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Add Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';
?>
