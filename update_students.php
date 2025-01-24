<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get class ID from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id === 0) {
    echo "<div class='alert alert-danger'>Invalid Class ID.</div>";
    include 'footer.php';
    exit;
}

// Fetch class details
$classQuery = "SELECT class_name FROM classes WHERE class_id = ?";
$stmt = $conn->prepare($classQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classDetails = $result->fetch_assoc();

if (!$classDetails) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    include 'footer.php';
    exit;
}

// Fetch students in the class
$studentQuery = "SELECT 
                    student_id,
                    family_code,
                    student_name,
                    gr_no,
                    gender,
                    father_name,
                    father_cell_no,
                    dob,
                    date_of_admission,
                    status,
                    session,
                    religion,
                    monthly_fee
                FROM students
                WHERE status = 'active' AND class_id = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$studentsResult = $stmt->get_result();
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Classes</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Update Student Info</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Update Class Details: <?php echo htmlspecialchars($classDetails['class_name']); ?></h4>
                        <form action="update_students_action.php" method="POST">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Student ID</th>
                                            <th>Family Code</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Father's Name</th>
                                            <th>Father's Cell No</th>
                                            <th>Date of Birth</th>
                                            <th>Date of Admission</th>
                                            <th>Session</th>
                                            <th>Religion</th>
                                            <th>Monthly Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($studentsResult->num_rows > 0) {
                                            $sno = 1;
                                            while ($student = $studentsResult->fetch_assoc()) {
                                                echo "<tr>
                                                        <td>" . $sno++ . "</td>
                                                        <td><input type='text' name='student_id[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['student_id']) . "' class='form-control' readonly></td>
                                                        <td><input type='text' name='family_code[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['family_code']) . "' class='form-control'></td>
                                                        <td><input type='text' name='student_name[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['student_name']) . "' class='form-control'></td>
                                                        <td>
                                                            <select name='gender[" . $student['student_id'] . "]' class='form-control'>
                                                                <option value='Male'" . ($student['gender'] == 'Male' ? ' selected' : '') . ">Male</option>
                                                                <option value='Female'" . ($student['gender'] == 'Female' ? ' selected' : '') . ">Female</option>
                                                            </select>
                                                        </td>
                                                        <td><input type='text' name='father_name[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['father_name']) . "' class='form-control'></td>
                                                        <td><input type='text' name='father_cell_no[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['father_cell_no']) . "' class='form-control'></td>
                                                        <td><input type='date' name='dob[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['dob']) . "' class='form-control'></td>
                                                        <td><input type='date' name='date_of_admission[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['date_of_admission']) . "' class='form-control'></td>
                                                        <td><input type='number' name='session[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['session']) . "' class='form-control'></td>
                                                        <td><input type='text' name='religion[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['religion']) . "' class='form-control'></td>
                                                        <td><input type='number' name='monthly_fee[" . $student['student_id'] . "]' value='" . htmlspecialchars($student['monthly_fee']) . "' class='form-control'></td>
                                                      </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='12'>No students found in this class.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="classwise.php" class="btn btn-secondary">Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
