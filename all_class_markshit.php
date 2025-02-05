<?php
// all_class_result.php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Class, Section, and Exam options
$classes  = $conn->query("SELECT * FROM classes ORDER BY class_name");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
$exams    = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");

// Check if the form was submitted
$class_id   = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
$exam_id    = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Generate All Class Marksheets</h4>
        <!-- Selection Form -->
        <form method="GET" action="">
            <div class="row">
                <!-- Class Selection -->
                <div class="col-md-4">
                    <label>Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id']; ?>" <?= ($class_id === intval($class['class_id'])) ? 'selected' : ''; ?>>
                                <?= $class['class_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Section Selection -->
                <div class="col-md-4">
                    <label>Section</label>
                    <select name="section_id" id="section_id" class="form-control" required>
                        <option value="">Select Section</option>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <option value="<?= $section['section_id']; ?>" <?= ($section_id === intval($section['section_id'])) ? 'selected' : ''; ?>>
                                <?= $section['section_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Exam Selection -->
                <div class="col-md-4">
                    <label>Exam</label>
                    <select name="exam_id" id="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                        <?php while ($exam = $exams->fetch_assoc()): ?>
                            <option value="<?= $exam['exam_id']; ?>" <?= ($exam_id === intval($exam['exam_id'])) ? 'selected' : ''; ?>>
                                <?= $exam['exam_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Submit Button -->
                <div class="col-md-4 mt-4">
                    <button type="submit" class="btn btn-primary">Generate All Marksheets</button>
                </div>
            </div>
        </form>

        <?php if ($class_id && $section_id && $exam_id):
            // Fetch class, section, and exam info for display
            $classInfo   = $conn->query("SELECT class_name FROM classes WHERE class_id = $class_id")->fetch_assoc();
            $sectionInfo = $conn->query("SELECT section_name FROM sections WHERE section_id = $section_id")->fetch_assoc();
            $examInfo    = $conn->query("SELECT exam_name FROM exams WHERE exam_id = $exam_id")->fetch_assoc();

            // Fetch all students in the selected class & section
            $studentsQuery = "SELECT * FROM students WHERE class_id = $class_id AND section_id = $section_id ORDER BY student_name";
            $studentsResult = $conn->query($studentsQuery);
        ?>
            <!-- Export All Marksheets Button -->
            <form method="POST" action="export_all_marksheets_csv.php">
                <input type="hidden" name="class_id" value="<?= intval($_GET['class_id']); ?>">
                <input type="hidden" name="section_id" value="<?= intval($_GET['section_id']); ?>">
                <input type="hidden" name="exam_id" value="<?= intval($_GET['exam_id']); ?>">
                <button type="submit" class="btn btn-success">Export All Marksheets as CSV</button>
            </form>


            <!-- Display Marksheets for All Students -->
            <div id="all-marksheets">
                <?php while ($student = $studentsResult->fetch_assoc()):
                    $student_id = intval($student['student_id']);

                    // Fetch student details (for example, student_name, family_code, father_name)
                    // You can adjust the query and details as required.
                    $studentName = $student['student_name'];
                    $familyCode  = $student['family_code'];
                    $fatherName  = $student['father_name'];

                    // Fetch student's marks for the selected exam
                    $marksQuery = "
                    SELECT sub.subject_name, em.marks_obtained, em.max_marks
                    FROM exam_marks em
                    JOIN subjects sub ON em.subject_id = sub.subject_id
                    WHERE em.student_id = $student_id AND em.exam_id = $exam_id
                    ORDER BY sub.subject_name";
                    $marksResult = $conn->query($marksQuery);

                    // Calculate totals for overall performance
                    $total_obtained = 0;
                    $total_max = 0;
                ?>
                    <div class="marksheet" style="border:1px solid #000; margin:20px 0; padding:10px;">
                        <!-- Header Section -->
                        <div style="text-align:center;">
                            <h1 style="margin:0; font-size:18px;">Hazara Public School & College Jamber</h1>
                            <p style="margin:2px 0; font-size:12px;"><?= htmlspecialchars($examInfo['exam_name']); ?> - Result Card</p>
                            <p style="margin:2px 0; font-size:12px;">Class: <?= htmlspecialchars($classInfo['class_name']); ?> (<?= htmlspecialchars($sectionInfo['section_name']); ?>)</p>
                            <p style="margin:2px 0; font-size:12px;">Date: <?= date('Y-m-d'); ?></p>
                        </div>
                        <!-- Student Information -->
                        <div style="margin:10px 0; font-size:14px;">
                            <p><strong>Name:</strong> <?= htmlspecialchars($studentName); ?></p>
                            <p><strong>Family Code:</strong> <?= htmlspecialchars($familyCode); ?></p>
                            <p><strong>Father Name:</strong> <?= htmlspecialchars($fatherName); ?></p>
                        </div>
                        <!-- Marks Table -->
                        <?php if ($marksResult->num_rows > 0): ?>
                            <table style="width:100%; border-collapse:collapse; margin-bottom:10px;" border="1">
                                <thead>
                                    <tr style="background:#ddd; text-align:center;">
                                        <th>Subject</th>
                                        <th>Marks Obtained</th>
                                        <th>Total Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $marksResult->fetch_assoc()):
                                        $percentage = ($row['max_marks'] > 0) ? ($row['marks_obtained'] / $row['max_marks'] * 100) : 0;
                                        // Determine grade
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
                                            <td style="padding:5px; text-align:center;"><?= htmlspecialchars($row['subject_name']); ?></td>
                                            <td style="padding:5px; text-align:center;"><?= $row['marks_obtained']; ?></td>
                                            <td style="padding:5px; text-align:center;"><?= $row['max_marks']; ?></td>
                                            <td style="padding:5px; text-align:center;"><?= number_format($percentage, 2); ?>%</td>
                                            <td style="padding:5px; text-align:center;"><?= $grade; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <!-- Overall Performance -->
                            <?php
                            $overall_percentage = ($total_max > 0) ? ($total_obtained / $total_max * 100) : 0;
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
                            <div style="font-size:14px; text-align:center;">
                                <p><strong>Total Marks Obtained:</strong> <?= $total_obtained; ?> / <?= $total_max; ?></p>
                                <p><strong>Overall Percentage:</strong> <?= number_format($overall_percentage, 2); ?>%</p>
                                <p><strong>Final Grade:</strong> <?= $overall_grade; ?></p>
                            </div>
                        <?php else: ?>
                            <p style="text-align:center;">No marks found for this student for the selected exam.</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Optional: Add your JavaScript to dynamically load students if desired.
    // (For example, similar to your earlier get_students_by_class_section functionality.)
</script>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #all-marksheets,
        #all-marksheets * {
            visibility: visible;
        }

        #all-marksheets {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<?php require_once 'footer.php'; ?>