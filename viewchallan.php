<?php
include 'confiq.php';  // Database connection
include 'header.php';   // Optional: Add header
include 'sidebar.php';  // Optional: Add sidebar

// Fetch classes and sections for filtering (optional)
$classes_query = "SELECT class_id, class_name FROM classes ORDER BY class_name ASC";
$classes_result = $conn->query($classes_query);

$sections_query = "SELECT section_id, section_name FROM sections ORDER BY section_name ASC";
$sections_result = $conn->query($sections_query);

// Initialize variables for filtering
$class_filter = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$section_filter = isset($_GET['section_id']) ? $_GET['section_id'] : '';

// Build the WHERE clause for filtering (optional)
$where_clause = "";
if ($class_filter) {
    $where_clause .= " AND students.class_id = '$class_filter'";
}
if ($section_filter) {
    $where_clause .= " AND students.section_id = '$section_filter'";
}

// Fetch students and their payment status
$query = "
    SELECT 
        students.student_id, 
        students.student_name, 
        classes.class_name, 
        sections.section_name, 
        IFNULL(payments.payment_status, 'unpaid') AS payment_status
    FROM students
    JOIN classes ON students.class_id = classes.class_id
    JOIN sections ON students.section_id = sections.section_id
    LEFT JOIN payments ON payments.student_id = students.student_id
    WHERE 1 $where_clause
    ORDER BY students.student_name ASC
";
$students_result = $conn->query($query);
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">View All Students</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">All Students and Payment Status</h4>

                        <!-- Filter Form -->
                        <form method="get" action="viewstudents.php">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="class-id">Class</label>
                                    <select class="form-control" id="class-id" name="class_id">
                                        <option value="">All Classes</option>
                                        <?php while ($row = $classes_result->fetch_assoc()): ?>
                                            <option value="<?= $row['class_id'] ?>" <?= ($class_filter == $row['class_id']) ? 'selected' : '' ?>><?= $row['class_name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="section-id">Section</label>
                                    <select class="form-control" id="section-id" name="section_id">
                                        <option value="">All Sections</option>
                                        <?php while ($row = $sections_result->fetch_assoc()): ?>
                                            <option value="<?= $row['section_id'] ?>" <?= ($section_filter == $row['section_id']) ? 'selected' : '' ?>><?= $row['section_name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                </div>
                            </div>
                        </form>

                        <!-- Student List -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Payment Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($students_result->num_rows > 0): ?>
                                    <?php while ($row = $students_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['student_id'] ?></td>
                                            <td><?= $row['student_name'] ?></td>
                                            <td><?= $row['class_name'] ?></td>
                                            <td><?= $row['section_name'] ?></td>
                                            <td>
                                                <?php if ($row['payment_status'] == 'paid'): ?>
                                                    <span class="badge badge-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Unpaid</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No Students Found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';  // Optional: Add footer
?>
