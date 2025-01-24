<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Fetch sessions dynamically with prepared statements
$session_query = "SELECT * FROM sessions ORDER BY session_name ASC";
$session_result = $conn->query($session_query);

// Fetch classes dynamically with prepared statements
$class_query = "SELECT * FROM classes ORDER BY class_name ASC";
$class_result = $conn->query($class_query);

// Fetch sections dynamically with prepared statements
$section_query = "SELECT * FROM sections ORDER BY section_name ASC";
$section_result = $conn->query($section_query);
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Promote/Demote Students</h4>
                        <!-- Search Form -->
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="session">Session</label>
                                    <select name="session" id="session" class="form-control" required>
                                        <option value="">Select a session</option>
                                        <?php
                                        if ($session_result->num_rows > 0) {
                                            while ($row = $session_result->fetch_assoc()) {
                                                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['session_name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option>No sessions found</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="class">Class</label>
                                    <select name="class" id="class" class="form-control" required>
                                        <option value="">Select a class</option>
                                        <?php
                                        if ($class_result->num_rows > 0) {
                                            while ($row = $class_result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row['class_id']) . "'>" . htmlspecialchars($row['class_name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option>No classes found</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="section">Section</label>
                                    <select name="section" id="section" class="form-control" required>
                                        <option value="">Select a section</option>
                                        <?php
                                        if ($section_result->num_rows > 0) {
                                            while ($row = $section_result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row['section_id']) . "'>" . htmlspecialchars($row['section_name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option>No sections found</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="search" class="btn btn-primary mt-3">Search</button>
                        </form>
                    </div>

                    <!-- Display Students -->
                    <?php
                    if (isset($_POST['search'])) {
                        $session_id = $_POST['session'];
                        $class_id = $_POST['class'];
                        $section_id = $_POST['section'];

                        $student_query = "SELECT 
                        students.student_id, 
                        students.student_name, 
                        students.session, 
                        sessions.session_name,
                        students.class_id, 
                        students.section_id, 
                        students.status, 
                        sections.section_name,
                        students.gender, 
                        students.father_name, 
                        students.email ,
                        classes.class_name
                  FROM students 
                  JOIN sessions ON students.session = sessions.id 
                  JOIN sections ON students.section_id = sections.section_id 
                  JOIN classes ON students.class_id = classes.class_id
                  WHERE students.session = ? AND students.class_id = ? AND students.section_id = ?";

                        $stmt = $conn->prepare($student_query);

                        if ($stmt === false) {
                            echo "Error preparing statement: " . $conn->error;
                        }

                        $stmt->bind_param('iii', $session_id, $class_id, $section_id);
                        $stmt->execute();
                        $student_result = $stmt->get_result();

                        if ($student_result->num_rows > 0) {
                            echo '<form action="" method="POST">';
                            echo '<table class="table table-striped mt-3">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Select</th>';
                            echo '<th>Student ID</th>';
                            echo '<th>Student Name</th>';
                            echo '<th>Session</th>';
                            echo '<th>Class</th>';
                            echo '<th>Section</th>';
                            echo '<th>Status</th>';
                            echo '<th>Gender</th>';
                            echo '<th>Father Name</th>';
                            echo '<th>Email</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            while ($student = $student_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><input type='checkbox' name='students[]' value='" . htmlspecialchars($student['student_id']) . "'></td>";
                                echo "<td>" . htmlspecialchars($student['student_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['student_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['session_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['class_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['section_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['status']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['gender']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['father_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['email']) . "</td>";
                                echo "</tr>";
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '<button type="submit" name="promote" class="btn btn-success mt-3">Promote Selected Students</button>';
                            echo '<button type="submit" name="demote" class="btn btn-danger mt-3">Demote Selected Students</button>';
                            echo '</form>';
                        } else {
                            echo "<p>No students found in the selected session, class, and section.</p>";
                        }
                    }

                    // Promote/Demote Logic
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['students'])) {
                        $student_ids = $_POST['students'];

                        if (isset($_POST['promote'])) {
                            $update_query = "UPDATE students SET class_id = class_id + 1 WHERE student_id = ?";
                        } elseif (isset($_POST['demote'])) {
                            $update_query = "UPDATE students SET class_id = class_id - 1 WHERE student_id = ?";
                        }

                        if (!empty($update_query)) {
                            $stmt = $conn->prepare($update_query);

                            if ($stmt === false) {
                                echo "Error preparing statement: " . $conn->error;
                            } else {
                                foreach ($student_ids as $id) {
                                    $stmt->bind_param('i', $id);
                                    $stmt->execute();
                                }
                                echo '<div class="alert alert-success mt-3">Selected students have been successfully updated.</div>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
