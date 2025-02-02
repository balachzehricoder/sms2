<?php
include 'confiq.php';    // Database connection
include 'header.php';    // Header layout
include 'sidebar.php';   // Sidebar navigation
error_reporting(E_ALL);

// Handle form submission to save performance ratings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_performance'])) {
    $evaluation_date = date('Y-m-d');  // Set the current date for evaluation

    foreach ($_POST['performance'] as $student_id => $criteria_scores) {
        foreach ($criteria_scores as $criteria_id => $score) {
            // Insert or update student performance
            $stmt = $conn->prepare("
                INSERT INTO student_performance (student_id, criteria_id, score, evaluation_date)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE score = VALUES(score), evaluation_date = VALUES(evaluation_date)
            ");

            if (!$stmt) {
                echo "Error preparing statement: " . $conn->error;
                exit;
            }

            $stmt->bind_param("iiis", $student_id, $criteria_id, $score, $evaluation_date);

            if (!$stmt->execute()) {
                echo "Error executing statement: " . $stmt->error;
                exit;
            }

            $stmt->close();
        }
    }

    $message = "Performance ratings saved successfully!";
}

// Fetch sessions, classes, sections, and exams for the filters
$sessions = $conn->query("SELECT * FROM sessions ORDER BY start_date DESC");
$classes  = $conn->query("SELECT * FROM classes ORDER BY class_name");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
$exams    = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");

// Fetch students and performance criteria if filters are applied
$students = [];
$criteria = [];
if (isset($_GET['session_id'], $_GET['class_id'], $_GET['section_id'], $_GET['exam_id'])) {
    $session_id = intval($_GET['session_id']);
    $class_id   = intval($_GET['class_id']);
    $section_id = intval($_GET['section_id']);
    $exam_id    = intval($_GET['exam_id']);

    // Fetch students with family_code and father_name
    $studentsQuery = "
        SELECT student_id, student_name, family_code, father_name
        FROM students 
        WHERE session = $session_id AND class_id = $class_id AND section_id = $section_id 
        ORDER BY student_name";
    $students = $conn->query($studentsQuery)->fetch_all(MYSQLI_ASSOC);

    // Fetch performance criteria
    $criteriaQuery = "SELECT id, criteria_name FROM performance_criteria ORDER BY criteria_name";
    $criteria = $conn->query($criteriaQuery)->fetch_all(MYSQLI_ASSOC);
}
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Student Performance Grader</h4>

        <!-- Display any messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form method="GET" action="">
            <div class="row">
                <!-- Session -->
                <div class="col-md-3">
                    <label>Session</label>
                    <select name="session_id" class="form-control" required>
                        <option value="">Select Session</option>
                        <?php while ($session = $sessions->fetch_assoc()): ?>
                            <option value="<?= $session['id']; ?>" <?= (isset($_GET['session_id']) && $_GET['session_id'] == $session['id']) ? 'selected' : ''; ?>>
                                <?= $session['session_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Class -->
                <div class="col-md-3">
                    <label>Class</label>
                    <select name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id']; ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                                <?= $class['class_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Section -->
                <div class="col-md-3">
                    <label>Section</label>
                    <select name="section_id" class="form-control" required>
                        <option value="">Select Section</option>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <option value="<?= $section['section_id']; ?>" <?= (isset($_GET['section_id']) && $_GET['section_id'] == $section['section_id']) ? 'selected' : ''; ?>>
                                <?= $section['section_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Exam -->
                <div class="col-md-3">
                    <label>Exam</label>
                    <select name="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                        <?php while ($exam = $exams->fetch_assoc()): ?>
                            <option value="<?= $exam['exam_id']; ?>" <?= (isset($_GET['exam_id']) && $_GET['exam_id'] == $exam['exam_id']) ? 'selected' : ''; ?>>
                                <?= $exam['exam_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <!-- Display Performance Grading Table -->
        <?php if (!empty($students) && !empty($criteria)): ?>
            <form method="POST" action="">
                <input type="hidden" name="save_performance" value="1">

                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Family Code</th>
                            <th>Father Name</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th><?= htmlspecialchars($criterion['criteria_name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['student_name']); ?></td>
                                <td><?= htmlspecialchars($student['family_code']); ?></td>
                                <td><?= htmlspecialchars($student['father_name']); ?></td>
                                <?php foreach ($criteria as $criterion): ?>
                                    <td>
                                        <input type="number" class="form-control" name="performance[<?= $student['student_id']; ?>][<?= $criterion['id']; ?>]"
                                            min="1" max="5" required>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success mt-3">Save Performance</button>
            </form>
        <?php elseif (isset($_GET['session_id'])): ?>
            <p>No students or criteria found for the selected filters.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>