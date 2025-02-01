<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Fetch all subjects
$subjectsQuery = "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name";
$subjects = $conn->query($subjectsQuery);
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Subjects</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Subjects List</h4>

                        <button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-subject">
                            <i class="fa fa-plus-circle"></i> Add New Subject
                        </button>

                        <!-- Subjects Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Subject Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($subjects->num_rows > 0):
                                        $sno = 1;
                                        while ($row = $subjects->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $sno++; ?></td>
                                                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#edit-subject-<?php echo $row['subject_id']; ?>">Edit</button>
                                                    <a href="delete_subject.php?subject_id=<?php echo urlencode($row['subject_id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                                                </td>
                                            </tr>

                                            <!-- MODAL: Edit Subject -->
                                            <div class="modal fade" id="edit-subject-<?php echo $row['subject_id']; ?>">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Subject</h5>
                                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="edit_subject.php" method="POST">
                                                                <input type="hidden" name="subject_id" value="<?php echo $row['subject_id']; ?>">
                                                                <div class="form-group">
                                                                    <label for="subject_name">Subject Name</label>
                                                                    <input type="text" class="form-control" name="subject_name" value="<?php echo htmlspecialchars($row['subject_name']); ?>" required>
                                                                </div>
                                                                <button type="submit" class="btn btn-primary">Update Subject</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END MODAL -->
                                        <?php endwhile;
                                    else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No subjects found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div> <!-- End Table -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Add New Subject -->
<div class="modal fade" id="add-new-subject">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="add_subject.php" method="POST">
                    <div class="form-group">
                        <label for="subject_name">Subject Name</label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END MODAL -->

<?php include 'footer.php'; ?>