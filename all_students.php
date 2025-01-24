<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Update status if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && isset($_POST['action'])) {
    $studentId = intval($_POST['student_id']);
    $newStatus = $_POST['action'] === 'struck_off' ? 'struck_off' : 'active';

    $updateQuery = "UPDATE students SET status = ? WHERE student_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('si', $newStatus, $studentId);
    $stmt->execute();
    $stmt->close();
}

// Fetch all students from the database
$studentQuery = "SELECT
                    student_id,
                    family_code,
                    gr_no,
                    student_name,
                    gender,
                    class_id,
                    section_id,
                    date_of_admission,
                    status
                 FROM students
                 ORDER BY date_of_admission DESC";

$studentResult = $conn->query($studentQuery);
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">All Students</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="flex justify-content-around">
							<h4 class="card-title">All Students</h4>
							<a href="export_all_students.php" class="btn btn-success">Export to Excel</a>

						</div>
						
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Family Code</th>
                                        <th>Student Name</th>
                                        <th>Gender</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Date of Admission</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($studentResult->num_rows > 0) {
                                        $sno = 1;
                                        while ($studentRow = $studentResult->fetch_assoc()) {
                                            $status = htmlspecialchars($studentRow['status']);
                                            $toggleAction = $status === 'active' ? 'struck_off' : 'active';
                                            $buttonText = $status === 'active' ? 'Deactivate' : 'Activate';

                                            echo "<tr>
                                                    <td>" . $sno++ . "</td>
                                                    <td>" . htmlspecialchars($studentRow['family_code']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['student_name']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['gender']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['class_id']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['section_id']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['date_of_admission']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['status']) . "</td>
                                                    <td>
                                                        <form method='POST' action='' style='display:inline;'>
                                                            <input type='hidden' name='student_id' value='" . $studentRow['student_id'] . "'>
                                                            <input type='hidden' name='action' value='" . $toggleAction . "'>
                                                            <button type='submit' class='btn btn-warning'>" . $buttonText . "</button>
                                                        </form>
                                                        <a href='view_student.php?student_id=" . $studentRow['student_id'] . "' class='btn btn-primary'>View</a>
                                                        <a href='update_student.php?student_id=" . $studentRow['student_id'] . "' class='btn btn-success'>Update</a>
                                                        <a href='delete_student.php?student_id=" . $studentRow['student_id'] . "' class='btn btn-danger'>Delete</a>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9'>No students found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
