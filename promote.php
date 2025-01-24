<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Fetch sessions dynamically with prepared statement
$session_query = "SELECT * FROM sessions ORDER BY session_name ASC";
$session_result = $conn->query($session_query);

// Fetch classes dynamically with prepared statement
$class_query = "SELECT * FROM classes ORDER BY class_name ASC";
$class_result = $conn->query($class_query);

// Fetch sections dynamically with prepared statement
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
                                                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['class_name']) . "</option>";
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
                                                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['section_name']) . "</option>";
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
                        // Get values from POST
                        $session_id = $_POST['session'];
                        $class_id = $_POST['class'];
                        $section_id = $_POST['section'];

                        // Debugging: Print out the selected values to ensure they are correct
                        // Remove this once the issue is fixed
                        // echo "Session ID: $session_id, Class ID: $class_id, Section ID: $section_id<br>";

                        // Use prepared statements to avoid SQL injection
                        $student_query = "SELECT * FROM students WHERE session = ? AND class_id = ? AND section_id = ?";
                        $stmt = $conn->prepare($student_query);
                        
                        // Check for preparation errors
                        if ($stmt === false) {
                            echo "Error preparing statement: " . $conn->error;
                        }
                        
                        // Bind parameters and execute query
                        $stmt->bind_param('iii', $session_id, $class_id, $section_id);
                        $stmt->execute();
                        $student_result = $stmt->get_result();

                        // Check if students are found
                        if ($student_result->num_rows > 0) {
                            echo '<form action="promote_students.php" method="POST">';
                            while ($student = $student_result->fetch_assoc()) {
                                echo "<div class='form-check'>
                                    <input type='checkbox' class='form-check-input' name='students[]' value='" . $student['id'] . "'>
                                    <label class='form-check-label'>" . htmlspecialchars($student['name']) . "</label>
                                </div>";
                            }
                            echo '<button type="submit" name="promote" class="btn btn-success mt-3">Promote Selected Students</button>';
                            echo '</form>';
                        } else {
                            echo "<p>No students found in the selected session, class, and section.</p>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
