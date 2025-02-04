<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch all teachers, sessions, and exams
$teachers = $conn->query("SELECT * FROM teachers ORDER BY teacher_name") or die("Error fetching teachers: " . $conn->error);
$sessions = $conn->query("SELECT * FROM sessions ORDER BY start_date DESC") or die("Error fetching sessions: " . $conn->error);
$exams    = $conn->query("SELECT * FROM exams ORDER BY start_date DESC") or die("Error fetching exams: " . $conn->error);
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Teacher Performance Report</h4>

        <!-- Selection Form -->
        <form method="GET" action="">
            <div class="row">
                <!-- Teacher Selection -->
                <div class="col-md-4">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Select Teacher</option>
                        <?php while ($teacher = $teachers->fetch_assoc()): ?>
                            <option value="<?= $teacher['teacher_id']; ?>" <?= (isset($_GET['teacher_id']) && $_GET['teacher_id'] == $teacher['teacher_id']) ? 'selected' : ''; ?>>
                                <?= $teacher['teacher_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Session Selection -->
                <div class="col-md-4">
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

                <!-- Exam Selection -->
                <div class="col-md-4">
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
                <div class="col-md-12 mt-4">
                    <button type="submit" class="btn btn-primary no-print">Generate Report</button>
                </div>
            </div>
        </form>

        <!-- Display Teacher Performance Report -->
        <?php
        if (isset($_GET['teacher_id'], $_GET['session_id'], $_GET['exam_id'])):
            $teacher_id = intval($_GET['teacher_id']);
            $session_id = intval($_GET['session_id']);
            $exam_id    = intval($_GET['exam_id']);

            // Fetch Teacher Name
            $teacherQuery = "SELECT teacher_name FROM teachers WHERE teacher_id = $teacher_id";
            $teacherResult = $conn->query($teacherQuery) or die("Error fetching teacher: " . $conn->error);
            $teacherRow = $teacherResult->fetch_assoc();

            // Fetch Performance Data (Class and Section) based on teacher, session, and exam
            $performanceQuery = "
            SELECT
                s.subject_name AS Subject,
                t.teacher_name AS Teacher,
                c.class_name AS Class,
                sec.section_name AS Section,
                COUNT(CASE WHEN em.marks_obtained >= 90 THEN 1 END) AS A,
                COUNT(CASE WHEN em.marks_obtained >= 80 AND em.marks_obtained < 90 THEN 1 END) AS B,
                COUNT(CASE WHEN em.marks_obtained >= 70 AND em.marks_obtained < 80 THEN 1 END) AS C,
                COUNT(CASE WHEN em.marks_obtained >= 60 AND em.marks_obtained < 70 THEN 1 END) AS D,
                COUNT(CASE WHEN em.marks_obtained < 60 OR em.marks_obtained IS NULL THEN 1 END) AS F,
                COUNT(*) AS Total_Students,
                ROUND(AVG(em.marks_obtained), 2) AS Average_Marks  -- Class average marks
            FROM
                teacher_subjects ts
            JOIN teachers t ON ts.teacher_id = t.teacher_id
            JOIN subjects s ON ts.subject_id = s.subject_id
            JOIN students stu ON ts.class_id = stu.class_id
            JOIN sections sec ON stu.section_id = sec.section_id
            JOIN classes c ON stu.class_id = c.class_id
            LEFT JOIN exam_marks em 
                ON em.student_id = stu.student_id 
                AND em.subject_id = s.subject_id
                AND em.exam_id = $exam_id
            WHERE
                ts.teacher_id = $teacher_id AND stu.session = $session_id
            GROUP BY
                s.subject_name,
                t.teacher_name,
                c.class_name,
                sec.section_name
            ORDER BY
                s.subject_name,
                c.class_name,
                sec.section_name";

            $performanceResult = $conn->query($performanceQuery) or die("Error fetching performance data: " . $conn->error);
        ?>

            <!-- Display Performance Report Table -->
            <div class="mt-4" id="teacher-report-content">
                <h5>Teacher Performance Report for <?= htmlspecialchars($teacherRow['teacher_name']); ?></h5>
                <!-- Export Button -->
                <div class="col-md-12 my-4">
                    <a href="export_report.php?teacher_id=<?= $_GET['teacher_id']; ?>&session_id=<?= $_GET['session_id']; ?>&exam_id=<?= $_GET['exam_id']; ?>" class="btn btn-success no-print">Export to Excel</a>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>A (90+)</th>
                            <th>B (80-89)</th>
                            <th>C (70-79)</th>
                            <th>D (60-69)</th>
                            <th>F (<60) </th>
                            <th>Total Students</th>
                            <th>Class Average Marks</th>
                            <th>Class Average Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $performanceResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Subject']); ?></td>
                                <td><?= htmlspecialchars($row['Teacher']); ?></td>
                                <td><?= htmlspecialchars($row['Class']); ?></td>
                                <td><?= htmlspecialchars($row['Section']); ?></td>
                                <td><?= $row['A']; ?></td>
                                <td><?= $row['B']; ?></td>
                                <td><?= $row['C']; ?></td>
                                <td><?= $row['D']; ?></td>
                                <td><?= $row['F']; ?></td>
                                <td><?= $row['Total_Students']; ?></td>
                                <td><?= $row['Average_Marks']; ?></td>
                                <td>
                                    <?php
                                    // Calculate the class average grade based on the average marks
                                    $average_marks = $row['Average_Marks'];
                                    if ($average_marks >= 90) {
                                        echo 'A+';
                                    } elseif ($average_marks >= 80) {
                                        echo 'A';
                                    } elseif ($average_marks >= 70) {
                                        echo 'B+';
                                    } elseif ($average_marks >= 60) {
                                        echo 'B';
                                    } elseif ($average_marks >= 50) {
                                        echo 'C+';
                                    } elseif ($average_marks >= 40) {
                                        echo 'C';
                                    } elseif ($average_marks >= 33) {
                                        echo 'D+';
                                    } elseif ($average_marks >= 20) {
                                        echo 'D';
                                    } else {
                                        echo 'Fail';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>