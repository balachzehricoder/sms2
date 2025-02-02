<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Sessions, Classes, Sections, and Exams
$sessions = $conn->query("SELECT * FROM sessions ORDER BY start_date DESC");
$classes  = $conn->query("SELECT * FROM classes ORDER BY class_name");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
$exams    = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Class-Oriented Report</h4>

        <!-- Selection Form -->
        <form method="GET" action="">
            <div class="row">
                <!-- Session Selection -->
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

                <!-- Class Selection -->
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

                <!-- Section Selection -->
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

                <!-- Exam Selection -->
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
                    <button type="submit" class="btn btn-primary no-print">Generate Report</button>
                </div>
            </div>
        </form>

        <!-- Display Class Report -->
        <?php
        if (isset($_GET['session_id'], $_GET['class_id'], $_GET['section_id'], $_GET['exam_id'])):
            $session_id = intval($_GET['session_id']);
            $class_id   = intval($_GET['class_id']);
            $section_id = intval($_GET['section_id']);
            $exam_id    = intval($_GET['exam_id']);

            // Fetch names from the database
            $sessionQuery = "SELECT session_name FROM sessions WHERE id = $session_id";
            $sessionResult = $conn->query($sessionQuery);
            $sessionRow = $sessionResult->fetch_assoc();

            $classQuery = "SELECT class_name FROM classes WHERE class_id = $class_id";
            $classResult = $conn->query($classQuery);
            $classRow = $classResult->fetch_assoc();

            $sectionQuery = "SELECT section_name FROM sections WHERE section_id = $section_id";
            $sectionResult = $conn->query($sectionQuery);
            $sectionRow = $sectionResult->fetch_assoc();

            $examQuery = "SELECT exam_name FROM exams WHERE exam_id = $exam_id";
            $examResult = $conn->query($examQuery);
            $examRow = $examResult->fetch_assoc();

            // Fetch Students
            $studentsQuery = "
        SELECT student_id, student_name 
        FROM students 
        WHERE session = $session_id 
          AND class_id = $class_id 
          AND section_id = $section_id 
        ORDER BY student_name";
            $studentsResult = $conn->query($studentsQuery);

            // Fetch Subjects assigned to the Class
            $subjectsQuery = "
        SELECT s.subject_id, s.subject_name 
        FROM class_subjects cs 
        JOIN subjects s ON cs.subject_id = s.subject_id 
        WHERE cs.class_id = $class_id 
        ORDER BY s.subject_name";
            $subjectsResult = $conn->query($subjectsQuery);
            $subjects = $subjectsResult->fetch_all(MYSQLI_ASSOC);

            if ($studentsResult->num_rows > 0 && count($subjects) > 0):
        ?>

                <div class="mt-4" id="class-report-content">
                    <h5>
                        Class Report for Session <?= htmlspecialchars($sessionRow['session_name']); ?>,
                        Class <?= htmlspecialchars($classRow['class_name']); ?>,
                        Section <?= htmlspecialchars($sectionRow['section_name']); ?>,
                        Exam <?= htmlspecialchars($examRow['exam_name']); ?>
                    </h5>

                    <!-- Export and Print Buttons -->
                    <form method="POST" action="export_class_report.php" class="mt-3 no-print">
                        <input type="hidden" name="session_id" value="<?= $session_id; ?>">
                        <input type="hidden" name="class_id" value="<?= $class_id; ?>">
                        <input type="hidden" name="section_id" value="<?= $section_id; ?>">
                        <input type="hidden" name="exam_id" value="<?= $exam_id; ?>">
                        <button type="submit" class="btn btn-success no-print">Export Report</button>
                        <button onclick="window.print()" class="btn btn-secondary no-print">Print Report</button>
                    </form>

                    <!-- Class Report Table -->
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <?php foreach ($subjects as $subject): ?>
                                    <th><?= htmlspecialchars($subject['subject_name']); ?></th>
                                <?php endforeach; ?>
                                <th>Total Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $studentsResult->fetch_assoc()):
                                $total_obtained = 0;
                                $total_max      = 0;
                                $subjects_graded = 0;  // Counter for graded subjects
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['student_name']); ?></td>
                                    <?php foreach ($subjects as $subject):
                                        $marksQuery = "
                                SELECT marks_obtained, max_marks 
                                FROM exam_marks 
                                WHERE student_id = {$student['student_id']} 
                                  AND subject_id = {$subject['subject_id']} 
                                  AND exam_id = $exam_id";
                                        $marksResult = $conn->query($marksQuery)->fetch_assoc();

                                        if ($marksResult) {
                                            $marks_obtained = $marksResult['marks_obtained'];
                                            $max_marks      = $marksResult['max_marks'];
                                            $total_obtained += $marks_obtained;
                                            $total_max      += $max_marks;
                                            $subjects_graded++;
                                        }
                                    ?>
                                        <td>
                                            <?php if ($marksResult): ?>
                                                <?= $marks_obtained; ?> / <?= $max_marks; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not Graded</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <!-- Total, Percentage, Grade -->
                                    <td><?= $total_obtained; ?> / <?= $total_max; ?></td>
                                    <?php
                                    if ($subjects_graded > 0) {
                                        $percentage = ($total_obtained / $total_max) * 100;
                                    } else {
                                        $percentage = 0;  // No graded subjects, percentage is 0
                                    }

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
                                    <td><?= number_format($percentage, 2); ?>%</td>
                                    <td><?= $grade; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <p>No students or subjects found for the selected criteria.</p>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- Print-specific CSS to hide unwanted elements -->
<style>
    @media print {

        .no-print,
        .sidebar,
        .header,
        .footer {
            display: none !important;
        }

        /* Hide everything else except the report */
        body * {
            visibility: hidden;
        }

        #class-report-content,
        #class-report-content * {
            visibility: visible;
        }

        #class-report-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>

<?php require_once 'footer.php'; ?>