<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch filters for Class, Section, Session, Exam, and Subject
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
$sessions = $conn->query("SELECT * FROM sessions ORDER BY start_date DESC");
$exams = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">View Student Grades</h4>

        <!-- Filter Form -->
        <form method="GET" action="">
            <div class="row">
                <!-- Class Selection -->
                <div class="col-md-3">
                    <label>Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id']; ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                                <?= $class['class_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Section Selection -->
                <div class="col-md-3">
                    <label>Section</label>
                    <select name="section_id" id="section_id" class="form-control" required>
                        <option value="">Select Section</option>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <option value="<?= $section['section_id']; ?>" <?= (isset($_GET['section_id']) && $_GET['section_id'] == $section['section_id']) ? 'selected' : ''; ?>>
                                <?= $section['section_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Session Selection -->
                <div class="col-md-3">
                    <label>Session</label>
                    <select name="session_id" id="session_id" class="form-control" required>
                        <option value="">Select Session</option>
                        <?php while ($session = $sessions->fetch_assoc()): ?>
                            <option value="<?= $session['id']; ?>" <?= (isset($_GET['session_id']) && $_GET['session_id'] == $session['id']) ? 'selected' : ''; ?>>
                                <?= $session['session_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Exam Selection -->
                <div class="col-md-3">
                    <label>Exam</label>
                    <select name="exam_id" id="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                        <?php while ($exam = $exams->fetch_assoc()): ?>
                            <option value="<?= $exam['exam_id']; ?>" <?= (isset($_GET['exam_id']) && $_GET['exam_id'] == $exam['exam_id']) ? 'selected' : ''; ?>>
                                <?= $exam['exam_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Subject Selection (Dynamic) -->
                <div class="col-md-3 mt-3">
                    <label>Subject</label>
                    <select name="subject_id" id="subject_id" class="form-control" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">View Grades</button>
                </div>
            </div>
        </form>

        <!-- Display Grades Table -->
        <?php if (isset($_GET['class_id'], $_GET['section_id'], $_GET['session_id'], $_GET['exam_id'], $_GET['subject_id'])): ?>
            <?php
            $class_id = intval($_GET['class_id']);
            $section_id = intval($_GET['section_id']);
            $session_id = intval($_GET['session_id']);
            $exam_id = intval($_GET['exam_id']);
            $subject_id = intval($_GET['subject_id']);

            // Fetch student marks
            $gradesQuery = "
                SELECT s.student_name, em.marks_obtained, em.max_marks
                FROM exam_marks em
                JOIN students s ON em.student_id = s.student_id
                WHERE s.class_id = $class_id 
                  AND s.section_id = $section_id 
                  AND s.session = $session_id 
                  AND em.exam_id = $exam_id 
                  AND em.subject_id = $subject_id
                ORDER BY s.student_name";

            $gradesResult = $conn->query($gradesQuery);
            ?>

            <?php if ($gradesResult->num_rows > 0): ?>
                <div class="mt-4">
                    <h5>Grades for Selected Exam</h5>

                    <!-- Export Button Above the Table -->
                    <form method="POST" action="export_grades.php" class="mb-3">
                        <input type="hidden" name="class_id" value="<?= $class_id; ?>">
                        <input type="hidden" name="section_id" value="<?= $section_id; ?>">
                        <input type="hidden" name="session_id" value="<?= $session_id; ?>">
                        <input type="hidden" name="exam_id" value="<?= $exam_id; ?>">
                        <input type="hidden" name="subject_id" value="<?= $subject_id; ?>">
                        <button type="submit" class="btn btn-success">Export to Excel</button>
                    </form>

                    <!-- Grades Table -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $gradesResult->fetch_assoc()):
                                $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
                                $grade = '';

                                if ($percentage >= 90) {
                                    $grade = 'A+';
                                } elseif ($percentage >= 80) {
                                    $grade = 'A';
                                } elseif ($percentage >= 70) {
                                    $grade = 'B';
                                } elseif ($percentage >= 60) {
                                    $grade = 'C';
                                } elseif ($percentage >= 50) {
                                    $grade = 'D';
                                } else {
                                    $grade = 'F';
                                }
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                                    <td><?= $row['marks_obtained']; ?></td>
                                    <td><?= $row['max_marks']; ?></td>
                                    <td><?= number_format($percentage, 2); ?>%</td>
                                    <td><?= $grade; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="mt-4">No grades found for the selected criteria.</p>
            <?php endif; ?>


        <?php endif; ?>
    </div>
</div>

<script>
    // Fetch Subjects when Class is Selected
    document.getElementById("class_id").addEventListener("change", function() {
        let classId = this.value;
        fetch("get_subjects.php?class_id=" + classId)
            .then(response => response.text())
            .then(data => document.getElementById("subject_id").innerHTML = data);
    });

    // Auto-load subjects if class is already selected (for persistent filters)
    window.addEventListener('DOMContentLoaded', (event) => {
        if (document.getElementById("class_id").value) {
            let classId = document.getElementById("class_id").value;
            fetch("get_subjects.php?class_id=" + classId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("subject_id").innerHTML = data;
                    document.getElementById("subject_id").value = "<?= $_GET['subject_id'] ?? ''; ?>";
                });
        }
    });
</script>

<?php require_once 'footer.php'; ?>