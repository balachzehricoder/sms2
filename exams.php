<?php
include 'confiq.php';    // Database connection (MySQLi)
include 'header.php';    // Contains layout header, <body>, etc.
include 'sidebar.php';   // Sidebar navigation
error_reporting(E_ALL);

// ---------------------------------------------------------
// 1. Handle Form Submission for Creating a New Exam
//    (if you want to handle it in this same page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_exam'])) {
    // Retrieve form fields
    $exam_name = isset($_POST['exam_name']) ? trim($_POST['exam_name']) : '';
    $exam_type = isset($_POST['exam_type_id']) ? intval($_POST['exam_type_id']) : 0;
    $session_id = isset($_POST['session_id']) && $_POST['session_id'] !== ''
        ? intval($_POST['session_id'])
        : null;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;

    // Basic validation
    if (!empty($exam_name) && $exam_type > 0) {
        // Insert query (using prepared statement for safety)
        $sql = "INSERT INTO exams (exam_type_id, exam_name, start_date, end_date, session_id)
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        // i s s s i  => (int, string, string, string, int)
        mysqli_stmt_bind_param(
            $stmt,
            "isssi",
            $exam_type,
            $exam_name,
            $start_date,
            $end_date,
            $session_id
        );

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            $message = "Exam added successfully!";
        } else {
            $message = "Error adding exam.";
        }
        mysqli_stmt_close($stmt);

        // Optional: Redirect (or just show the message). 
        // Here we’ll show it in a query string so it survives refresh.
        echo "<script>
    alert('Exam added successfully!');
    window.location.href = 'exams.php';
</script>";
        exit;
    } else {
        // If validation fails
        $message = "Please fill in all required fields.";
        header("Location: exams.php?message=" . urlencode($message));
        exit();
    }
}

// ---------------------------------------------------------
// 2. Fetch Existing Exams
//    (Joining exam_types and sessions for more readable info)
$examQuery = "
    SELECT e.exam_id,
           e.exam_name,
           e.start_date,
           e.end_date,
           e.session_id,
           t.exam_type_name,
           s.session_name
    FROM exams e
    LEFT JOIN exam_types t ON e.exam_type_id = t.exam_type_id
    LEFT JOIN sessions s   ON e.session_id   = s.id
    ORDER BY e.exam_id DESC
";
$examResult = $conn->query($examQuery);

// ---------------------------------------------------------
// 3. Fetch Exam Types for the Modal Select
$examTypesQuery = "SELECT exam_type_id, exam_type_name FROM exam_types ORDER BY exam_type_name";
$examTypes = $conn->query($examTypesQuery);

// 4. Fetch Sessions for the Modal Select (optional)
$sessionQuery = "SELECT id, session_name FROM sessions ORDER BY session_name";
$allSessions = $conn->query($sessionQuery);

// ---------------------------------------------------------
// 5. Display Any “Success or Error” Message if Present
if (isset($_GET['message'])) {
    echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
}
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Exams</a></li>
            </ol>
        </div>
    </div>

    <!-- Container -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Card for the exam table -->
                <div class="card">
                    <div class="card-body">
                        <!-- Header section with "Add New Exam" button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">All Exams</h4>
                            <div>
                                <button type="button" class="btn btn-rounded btn-success" data-toggle="modal"
                                    data-target="#add-new-exam">
                                    <i class="fa fa-plus-circle"></i> Add New Exam
                                </button>
                            </div>
                        </div>

                        <!-- Exams Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>Exam ID</th>
                                        <th>Exam Name</th>
                                        <th>Exam Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Session</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($examResult && $examResult->num_rows > 0): ?>
                                        <?php while ($examRow = $examResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($examRow['exam_id']); ?></td>
                                                <td><?php echo htmlspecialchars($examRow['exam_name']); ?></td>
                                                <td><?php echo htmlspecialchars($examRow['exam_type_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($examRow['start_date'] ?: '—'); ?></td>
                                                <td><?php echo htmlspecialchars($examRow['end_date'] ?: '—'); ?></td>
                                                <td><?php echo htmlspecialchars($examRow['session_name'] ?: '—'); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                                        data-target="#edit-exam-modal" onclick="loadExamDataIntoModal(
                <?php echo $examRow['exam_id']; ?>,
                '<?php echo htmlspecialchars($examRow['exam_name']); ?>',
                '<?php echo htmlspecialchars($examRow['exam_type_name']); ?>',
                '<?php echo htmlspecialchars($examRow['start_date']); ?>',
                '<?php echo htmlspecialchars($examRow['end_date']); ?>',
                '<?php echo htmlspecialchars($examRow['session_id']); ?>'
            )">
                                                        Edit
                                                    </button>

                                                    <a href="delete_exam.php?exam_id=<?php echo urlencode($examRow['exam_id']); ?>"
                                                        class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this exam?');">
                                                        Delete
                                                    </a>
                                                </td>


                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">No exams found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- End Exams Table -->
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    <!-- End Container -->
</div>
<!-- END MAIN CONTENT -->

<!-- MODAL: Add New Exam -->
<div class="modal fade" id="add-new-exam">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Exam</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="basic-form">
                    <!-- 
                      If you want to handle form submission on the same page,
                      set action="" or "exams.php", and use method="POST". 
                      We'll add a hidden input 'create_exam' to differentiate. 
                    -->
                    <form action="" method="POST">
                        <input type="hidden" name="create_exam" value="1">

                        <!-- Exam Name -->
                        <div class="form-group">
                            <label for="exam_name">Exam Name</label>
                            <input type="text" class="form-control" id="exam_name" name="exam_name" required>
                        </div>

                        <!-- Exam Type Select -->
                        <div class="form-group">
                            <label for="exam_type_id">Exam Type</label>
                            <select class="form-control" id="exam_type_id" name="exam_type_id" required>
                                <option value="">-- Select Exam Type --</option>
                                <?php if ($examTypes && $examTypes->num_rows > 0): ?>
                                    <?php while ($type = $examTypes->fetch_assoc()): ?>
                                        <option value="<?php echo $type['exam_type_id']; ?>">
                                            <?php echo htmlspecialchars($type['exam_type_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="form-group">
                            <label for="start_date">Start Date (optional)</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>

                        <!-- End Date -->
                        <div class="form-group">
                            <label for="end_date">End Date (optional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>

                        <!-- Session Select (optional) -->
                        <div class="form-group">
                            <label for="session_id">Session (optional)</label>
                            <select class="form-control" id="session_id" name="session_id">
                                <option value="">-- No Session --</option>
                                <?php if ($allSessions && $allSessions->num_rows > 0): ?>
                                    <?php while ($ses = $allSessions->fetch_assoc()): ?>
                                        <option value="<?php echo $ses['id']; ?>">
                                            <?php echo htmlspecialchars($ses['session_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Add Exam</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- EDIT EXAM MODAL -->
<div class="modal fade" id="edit-exam-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Exam</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="basic-form">
                    <form action="update_exam" method="POST">
                        <!-- We'll differentiate Edit from Create using this hidden field -->
                        <input type="hidden" name="update_exam" value="1">
                        <!-- This hidden field holds the ID of the exam we are editing -->
                        <input type="hidden" id="edit_exam_id" name="exam_id" value="">

                        <!-- Exam Name -->
                        <div class="form-group">
                            <label for="edit_exam_name">Exam Name</label>
                            <input type="text" class="form-control" id="edit_exam_name" name="exam_name" required>
                        </div>

                        <!-- Exam Type -->
                        <div class="form-group">
                            <label for="edit_exam_type_id">Exam Type</label>
                            <select class="form-control" id="edit_exam_type_id" name="exam_type_id" required>
                                <option value="">-- Select Exam Type --</option>
                                <?php
                                // We'll reuse the same $examTypes query from earlier
                                // so make sure it’s still in scope or re-fetch it
                                $examTypesQuery = "SELECT exam_type_id, exam_type_name FROM exam_types ORDER BY exam_type_name";
                                $editExamTypes = $conn->query($examTypesQuery);
                                if ($editExamTypes && $editExamTypes->num_rows > 0) {
                                    while ($type = $editExamTypes->fetch_assoc()) {
                                        echo '<option value="' . $type['exam_type_id'] . '">'
                                            . htmlspecialchars($type['exam_type_name'])
                                            . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="form-group">
                            <label for="edit_start_date">Start Date (optional)</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date">
                        </div>

                        <!-- End Date -->
                        <div class="form-group">
                            <label for="edit_end_date">End Date (optional)</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date">
                        </div>

                        <!-- Session -->
                        <div class="form-group">
                            <label for="edit_session_id">Session (optional)</label>
                            <select class="form-control" id="edit_session_id" name="session_id">
                                <option value="">-- No Session --</option>
                                <?php
                                // Re-fetch sessions if needed
                                $sessionQuery = "SELECT id, session_name FROM sessions ORDER BY session_name";
                                $editSessions = $conn->query($sessionQuery);
                                if ($editSessions && $editSessions->num_rows > 0) {
                                    while ($ses = $editSessions->fetch_assoc()) {
                                        echo '<option value="' . $ses['id'] . '">'
                                            . htmlspecialchars($ses['session_name'])
                                            . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Update Exam</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END EDIT EXAM MODAL -->

<!-- END MODAL -->
<script>
    function loadExamDataIntoModal(examId, examName, examTypeName, startDate, endDate, sessionId) {
        // 1. Set hidden input to hold the exam ID
        document.getElementById('edit_exam_id').value = examId;

        // 2. Fill in the form fields
        document.getElementById('edit_exam_name').value = examName;

        // For exam type, you likely have an <option> value = exam_type_id
        // If you want to store the exam_type_id instead of name, adjust the parameter
        // For now, we do "placeholder approach"
        // document.getElementById('edit_exam_type_id').value = examTypeId;

        // Start/End date might be empty (null), so check before assigning
        if (startDate && startDate !== '—') {
            document.getElementById('edit_start_date').value = startDate;
        } else {
            document.getElementById('edit_start_date').value = '';
        }
        if (endDate && endDate !== '—') {
            document.getElementById('edit_end_date').value = endDate;
        } else {
            document.getElementById('edit_end_date').value = '';
        }

        // Session
        if (sessionId && sessionId !== '—') {
            document.getElementById('edit_session_id').value = sessionId;
        } else {
            document.getElementById('edit_session_id').value = '';
        }
    }
</script>

<?php

include 'footer.php';
?>