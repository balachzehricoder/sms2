<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Check if class_id is provided
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    die("❌ Invalid request.");
}

$class_id = intval($_GET['class_id']);

// Fetch class details
$classQuery = "SELECT class_name FROM classes WHERE class_id = ?";
$stmtClass = $conn->prepare($classQuery);
$stmtClass->bind_param("i", $class_id);
$stmtClass->execute();
$classResult = $stmtClass->get_result();
$class = $classResult->fetch_assoc();

if (!$class) {
    die("❌ Class not found.");
}

// Fetch all subjects
$subjectsQuery = "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name";
$allSubjects = $conn->query($subjectsQuery);
if (!$allSubjects) {
    die("❌ Error fetching subjects: " . $conn->error);
}

// Fetch currently assigned subjects
$assignedSubjectsQuery = "SELECT subject_id FROM class_subjects WHERE class_id = ?";
$stmtAssigned = $conn->prepare($assignedSubjectsQuery);
$stmtAssigned->bind_param("i", $class_id);
$stmtAssigned->execute();
$assignedSubjectsResult = $stmtAssigned->get_result();
$assignedSubjects = [];

while ($row = $assignedSubjectsResult->fetch_assoc()) {
    $assignedSubjects[] = $row['subject_id'];
}
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Edit Subjects for Class</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Subjects for Class: <?php echo htmlspecialchars($class['class_name']); ?></h4>

                        <form action="update_class_subject.php" method="POST">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">

                            <div class="form-group">
                                <label>Select Subjects</label>
                                <div class="row">
                                    <?php while ($subject = $allSubjects->fetch_assoc()): ?>
                                        <div class="col-md-4">
                                            <input type="checkbox" name="subject_ids[]"
                                                value="<?php echo $subject['subject_id']; ?>"
                                                <?php echo in_array($subject['subject_id'], $assignedSubjects) ? 'checked' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="class_subjects.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>