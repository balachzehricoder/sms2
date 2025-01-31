<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid request.");
}

$id = intval($_GET['id']);

// Fetch assignment details
$assignmentQuery = "
    SELECT ts.id, ts.teacher_id, ts.class_id, ts.subject_id, 
           t.teacher_name, c.class_name, s.subject_name
    FROM teacher_subjects ts
    JOIN teachers t ON ts.teacher_id = t.teacher_id
    JOIN classes c ON ts.class_id = c.class_id
    JOIN subjects s ON ts.subject_id = s.subject_id
    WHERE ts.id = ?";
$stmt = $conn->prepare($assignmentQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

if (!$assignment) {
    die("❌ Assignment not found.");
}

// Fetch all teachers, classes, and subjects
$teachers = $conn->query("SELECT teacher_id, teacher_name FROM teachers ORDER BY teacher_name");
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
$subjects = $conn->query("SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Teacher Assignment</h4>

                        <form action="update_teacher_subject.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div class="form-group">
                                <label for="teacher_id">Teacher</label>
                                <select class="form-control" id="teacher_id" name="teacher_id" required>
                                    <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                        <option value="<?php echo $teacher['teacher_id']; ?>"
                                            <?php echo ($teacher['teacher_id'] == $assignment['teacher_id']) ? "selected" : ""; ?>>
                                            <?php echo htmlspecialchars($teacher['teacher_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="class_id">Class</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <?php while ($class = $classes->fetch_assoc()): ?>
                                        <option value="<?php echo $class['class_id']; ?>"
                                            <?php echo ($class['class_id'] == $assignment['class_id']) ? "selected" : ""; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="subject_id">Subject</label>
                                <select class="form-control" id="subject_id" name="subject_id" required>
                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                        <option value="<?php echo $subject['subject_id']; ?>"
                                            <?php echo ($subject['subject_id'] == $assignment['subject_id']) ? "selected" : ""; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Assignment</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>