<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch classes, sections, sessions, exams, and subjects for selection
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name");
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
$sessions = $conn->query("SELECT * FROM sessions ORDER BY start_date DESC");
$exams = $conn->query("SELECT * FROM exams ORDER BY start_date DESC");

?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Assign Marks</h4>
        <form method="POST" action="save_marks.php">
            <div class="row">
                <!-- Class Selection -->
                <div class="col-md-3">
                    <label>Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id']; ?>"><?= $class['class_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Section Selection -->
                <div class="col-md-3">
                    <label>Section</label>
                    <select name="section_id" id="section_id" class="form-control" required>
                        <option value="">Select Section</option>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <option value="<?= $section['section_id']; ?>"><?= $section['section_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Session Selection -->
                <div class="col-md-3">
                    <label>Session</label>
                    <select name="session_id" id="session_id" class="form-control" required>
                        <option value="">Select Session</option>
                        <?php while ($session = $sessions->fetch_assoc()): ?>
                            <option value="<?= $session['id']; ?>"><?= $session['session_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Exam Selection -->
                <div class="col-md-3">
                    <label>Exam</label>
                    <select name="exam_id" id="exam_id" class="form-control" required>
                        <option value="">Select Exam</option>
                        <?php while ($exam = $exams->fetch_assoc()): ?>
                            <option value="<?= $exam['exam_id']; ?>"><?= $exam['exam_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Subject Selection (Dynamically Loaded) -->
                <div class="col-md-3 mt-3">
                    <label>Subject</label>
                    <select name="subject_id" id="subject_id" class="form-control" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>

                <!-- Button to Fetch Students -->
                <div class="col-md-3 mt-4">
                    <button type="button" class="btn btn-primary" id="fetch_students">Fetch Students</button>
                </div>
            </div>
        </form>

        <!-- Students List (Dynamically Loaded) -->
        <div id="students_table" class="mt-4"></div>
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

    // Fetch Students for Mark Entry
    document.getElementById("fetch_students").addEventListener("click", function() {
        let classId = document.getElementById("class_id").value;
        let sectionId = document.getElementById("section_id").value;
        let sessionId = document.getElementById("session_id").value;
        let examId = document.getElementById("exam_id").value;
        let subjectId = document.getElementById("subject_id").value;

        if (classId && sectionId && sessionId && examId && subjectId) {
            fetch(`get_students.php?class_id=${classId}&section_id=${sectionId}&session_id=${sessionId}&exam_id=${examId}&subject_id=${subjectId}`)
                .then(response => response.text())
                .then(data => document.getElementById("students_table").innerHTML = data);
        } else {
            alert("Please select all fields!");
        }
    });
</script>

<?php require_once 'footer.php'; ?>