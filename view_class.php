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
                    session_name,
                    religion
                FROM students JOIN sessions ON students.session = sessions.id
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
                <li class="breadcrumb-item active"><a href="javascript:void(0)">View Class</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Class Details: <?php echo htmlspecialchars($classDetails['class_name']); ?></h4>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($studentsResult->num_rows > 0) {
                                        $sno = 1;
                                        while ($student = $studentsResult->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>" . $sno++ . "</td>
                                                    <td>" . htmlspecialchars($student['student_id']) . "</td>
                                                    <td>" . htmlspecialchars($student['family_code']) . "</td>
                                                    <td>" . htmlspecialchars($student['student_name']) . "</td>
                                                    <td>" . htmlspecialchars($student['gender']) . "</td>
                                                    <td>" . htmlspecialchars($student['father_name']) . "</td>
                                                    <td>" . htmlspecialchars($student['father_cell_no']) . "</td>
                                                    <td>" . htmlspecialchars($student['dob']) . "</td>
                                                    <td>" . htmlspecialchars($student['date_of_admission']) . "</td>
                                                    <td>" . htmlspecialchars($student['session_name']) . "</td>
                                                    <td>" . htmlspecialchars($student['religion']) . "</td>

                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='10'>No students found in this class.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="classwise" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
