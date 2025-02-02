<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Class, Section, and Exam for selection
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
$exams = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Generate Student Marksheet</h4>

        <!-- Selection Form -->
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

                <!-- Student Selection (Dynamic) -->
                <div class="col-md-3">
                    <label>Student</label>
                    <select name="student_id" id="student_id" class="form-control" required>
                        <option value="">Select Student</option>
                        <!-- Students will be loaded dynamically via JavaScript -->
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">Generate Marksheet</button>
                </div>
            </div>
        </form>

        <!-- Display Marksheet -->
        <?php
        if (isset($_GET['student_id'], $_GET['exam_id'])):
            $student_id = intval($_GET['student_id']);
            $exam_id = intval($_GET['exam_id']);

            // Fetch student details
            $studentQuery = "SELECT student_name FROM students WHERE student_id = $student_id";
            $studentResult = $conn->query($studentQuery);
            $student = $studentResult->fetch_assoc();

            // Fetch student's marks
            $marksQuery = "
                SELECT sub.subject_name, em.marks_obtained, em.max_marks
                FROM exam_marks em
                JOIN subjects sub ON em.subject_id = sub.subject_id
                WHERE em.student_id = $student_id
                  AND em.exam_id = $exam_id
                ORDER BY sub.subject_name";
            $marksResult = $conn->query($marksQuery);

            // Fetch student performance data
            $performanceQuery = "
                SELECT pc.criteria_name, sp.score, cr.remark
                FROM student_performance sp
                JOIN performance_criteria pc ON sp.criteria_id = pc.id
                LEFT JOIN criteria_remarks cr ON sp.score = cr.score
                WHERE sp.student_id = $student_id
                ORDER BY pc.criteria_name";
            $performanceResult = $conn->query($performanceQuery);
        ?>

            <div class="mt-4" id="marksheet-content">
                <h5>Marksheet for <?= htmlspecialchars($student['student_name']); ?></h5>

                <!-- Export Marksheet Button -->
                <form method="POST" action="export_student_marksheet.php" class="mt-3 no-print">
                    <input type="hidden" name="student_id" value="<?= $student_id; ?>">
                    <input type="hidden" name="exam_id" value="<?= $exam_id; ?>">
                    <button type="submit" class="btn btn-success">Export Marksheet</button>
                    <button onclick="printMarksheet()" class="btn btn-secondary no-print">Print Marksheet</button>
                </form>

                <!-- Marks Section -->
                <?php if ($marksResult->num_rows > 0): ?>
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks Obtained</th>
                                <th>Total Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_obtained = 0;
                            $total_max = 0;

                            while ($row = $marksResult->fetch_assoc()):
                                $percentage = ($row['marks_obtained'] / $row['max_marks']) * 100;
                                $grade = '';

                                // Calculate Grade
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

                                $total_obtained += $row['marks_obtained'];
                                $total_max += $row['max_marks'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['subject_name']); ?></td>
                                    <td><?= $row['marks_obtained']; ?></td>
                                    <td><?= $row['max_marks']; ?></td>
                                    <td><?= number_format($percentage, 2); ?>%</td>
                                    <td><?= $grade; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Overall Performance -->
                    <?php
                    $overall_percentage = ($total_obtained / $total_max) * 100;
                    $overall_grade = '';

                    if ($overall_percentage >= 90) {
                        $overall_grade = 'A+';
                    } elseif ($overall_percentage >= 80) {
                        $overall_grade = 'A';
                    } elseif ($overall_percentage >= 70) {
                        $overall_grade = 'B';
                    } elseif ($overall_percentage >= 60) {
                        $overall_grade = 'C';
                    } elseif ($overall_percentage >= 50) {
                        $overall_grade = 'D';
                    } else {
                        $overall_grade = 'F';
                    }
                    ?>

                    <div class="mt-4">
                        <h5>Overall Performance</h5>
                        <p><strong>Total Marks Obtained:</strong> <?= $total_obtained; ?> / <?= $total_max; ?></p>
                        <p><strong>Overall Percentage:</strong> <?= number_format($overall_percentage, 2); ?>%</p>
                        <p><strong>Final Grade:</strong> <?= $overall_grade; ?></p>
                    </div>

                <?php else: ?>
                    <p>No marks found for the selected student and exam.</p>
                <?php endif; ?>

                <!-- Student Performance Section -->
                <div class="mt-4">
                    <h5>Student Performance Evaluation</h5>
                    <?php if ($performanceResult->num_rows > 0): ?>
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Criteria</th>
                                    <th>Score</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($performance = $performanceResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($performance['criteria_name']); ?></td>
                                        <td><?= $performance['score']; ?></td>
                                        <td><?= htmlspecialchars($performance['remark'] ?? ''); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No performance evaluation data found for this student.</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<script>
    // Fetch Students based on Class and Section
    document.getElementById("class_id").addEventListener("change", fetchStudents);
    document.getElementById("section_id").addEventListener("change", fetchStudents);

    function fetchStudents() {
        let classId = document.getElementById("class_id").value;
        let sectionId = document.getElementById("section_id").value;

        if (classId && sectionId) {
            fetch(`get_students_by_class_section.php?class_id=${classId}&section_id=${sectionId}`)
                .then(response => response.text())
                .then(data => document.getElementById("student_id").innerHTML = data);
        }
    }

    // Load students if class and section are already selected
    window.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById("class_id").value && document.getElementById("section_id").value) {
            fetchStudents();
        }
    });

    // Print Functionality
    function printMarksheet() {
        document.querySelectorAll('.no-print, .sidebar, .header, .footer').forEach(el => el.style.display = 'none');
        window.print();
        document.querySelectorAll('.no-print, .sidebar, .header, .footer').forEach(el => el.style.display = '');
    }
</script>

<style>
    @media print {

        .no-print,
        .sidebar,
        .header,
        .footer {
            display: none !important;
        }

        #marksheet-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
    }
</style>

<?php require_once 'footer.php'; ?>