<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Student Reports</h4>
                        <!-- Report Selection Form -->
                        <form action="" method="POST">
                            <div class="row">
                                <!-- Class Wise Report -->
                                <div class="col-md-4">
                                    <label for="class">Select Class</label>
                                    <select name="class" id="class" class="form-control">
                                        <option value="">Select a Class</option>
                                        <?php
                                        // Fetching classes dynamically
                                        $class_query = "SELECT * FROM classes ORDER BY class_name ASC";
                                        $class_result = $conn->query($class_query);
                                        if ($class_result->num_rows > 0) {
                                            while ($row = $class_result->fetch_assoc()) {
                                                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['class_name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <!-- Gender Wise Report -->
                                <div class="col-md-4">
                                    <label for="gender">Select Gender</label>
                                    <select name="gender" id="gender" class="form-control">
                                        <option value="">Select a Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                
                                <!-- All Students Report -->
                                <div class="col-md-4">
                                    <button type="submit" name="generate_report" class="btn btn-primary mt-3">Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Displaying the Report -->
                    <?php
                    if (isset($_POST['generate_report'])) {
                        $class_id = $_POST['class'];
                        $gender = $_POST['gender'];

                        if ($class_id && !$gender) {
                            // Class-wise report
                            $query = "SELECT * FROM students WHERE class_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param('i', $class_id);
                        } elseif (!$class_id && $gender) {
                            // Gender-wise report
                            $query = "SELECT * FROM students WHERE gender = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param('s', $gender);
                        } elseif (!$class_id && !$gender) {
                            // All students report
                            $query = "SELECT * FROM students";
                            $stmt = $conn->prepare($query);
                        }

                        // Execute query and display results
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            echo "<table class='table mt-3'>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Gender</th>
                                            <th>Session</th>
                                            <th>Section</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                            $i = 1;
                            while ($student = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $i++ . "</td>
                                        <td>" . htmlspecialchars($student['name']) . "</td>
                                        <td>" . htmlspecialchars($student['class_id']) . "</td>
                                        <td>" . htmlspecialchars($student['gender']) . "</td>
                                        <td>" . htmlspecialchars($student['session_id']) . "</td>
                                        <td>" . htmlspecialchars($student['section_id']) . "</td>
                                      </tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<p>No students found for the selected criteria.</p>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
