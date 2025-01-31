<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Fetch class-wise subject assignments
$assignmentsQuery = "
    SELECT c.class_id, c.class_name, 
           GROUP_CONCAT(s.subject_name ORDER BY s.subject_name SEPARATOR ', ') AS subjects
    FROM class_subjects cs
    JOIN classes c ON cs.class_id = c.class_id
    JOIN subjects s ON cs.subject_id = s.subject_id
    GROUP BY c.class_id
    ORDER BY c.class_name";
$assignments = $conn->query($assignmentsQuery);
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Class Subjects</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Assign Subjects to Classes</h4>

                        <button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-assignment">
                            <i class="fa fa-plus-circle"></i> Assign New Subject
                        </button>

                        <!-- Assignments Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Class Name</th>
                                        <th>Assigned Subjects</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($assignments->num_rows > 0):
                                        $sno = 1;
                                        while ($row = $assignments->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $sno++; ?></td>
                                                <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['subjects'] ?: 'No subjects assigned'); ?></td>
                                                <td>
                                                    <a href="edit_class_subject.php?class_id=<?php echo urlencode($row['class_id']); ?>"
                                                        class="btn btn-primary">
                                                        Edit
                                                    </a>
                                                    <a href="delete_class_subject.php?class_id=<?php echo urlencode($row['class_id']); ?>"
                                                        class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete all subjects for this class?');">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile;
                                    else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No subjects assigned to any class yet.</td>
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

<!-- MODAL: Add New Assignment -->
<div class="modal fade" id="add-new-assignment">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Subject to Class</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="add_class_subject.php" method="POST">
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select class="form-control" id="class_id" name="class_id" required>
                            <option value="">-- Select Class --</option>
                            <?php
                            $classesQuery = "SELECT class_id, class_name FROM classes ORDER BY class_name";
                            $classes = $conn->query($classesQuery);
                            while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $class['class_id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject_id">Subject</label>
                        <select class="form-control" id="subject_id" name="subject_id" required>
                            <option value="">-- Select Subject --</option>
                            <?php
                            $subjectsQuery = "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name";
                            $subjects = $conn->query($subjectsQuery);
                            while ($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $subject['subject_id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Assign Subject</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END MODAL -->

<?php include 'footer.php'; ?>