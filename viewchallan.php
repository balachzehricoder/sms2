<?php
include 'confiq.php';  // Database connection
include 'header.php';  // Optional
include 'sidebar.php'; // Optional

// 1) Fetch classes into an array
$classes = [];
$resClasses = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");
while ($row = $resClasses->fetch_assoc()) {
    $classes[] = $row;
}

// 2) Fetch sections into an array
$sections = [];
$resSections = $conn->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC");
while ($row = $resSections->fetch_assoc()) {
    $sections[] = $row;
}

// 3) Grab any GET filters from the URL
$class_filter   = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$section_filter = isset($_GET['section_id']) ? $_GET['section_id'] : '';

// 4) Build a WHERE clause based on filters
$where = "1";  // Always true, so we can append conditions
if (!empty($class_filter)) {
    $class_filter_esc = mysqli_real_escape_string($conn, $class_filter);
    $where .= " AND s.class_id = '$class_filter_esc'";
}
if (!empty($section_filter)) {
    $section_filter_esc = mysqli_real_escape_string($conn, $section_filter);
    $where .= " AND s.section_id = '$section_filter_esc'";
}

// 5) Query to get all challans and sum of payments
$query = "
    SELECT 
        c.challan_id,
        c.challan_month,
        c.challan_session,
        c.challan_date,
        c.due_date,
        c.total_amount,
        c.arrears,
        c.discount,
        c.final_amount,
        c.status AS challan_status,
        
        s.student_id,
        s.student_name,
        cl.class_name,
        sec.section_name,
        
        -- Sum of all payments for this challan
        COALESCE(SUM(p.amount_paid), 0) AS amount_paid
    FROM challans c
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    LEFT JOIN payments p ON p.challan_id = c.challan_id
    WHERE $where
    GROUP BY c.challan_id
    ORDER BY c.challan_id DESC
";

$result = $conn->query($query);
?>

<div class="content-body">
    <div class="container-fluid">
        <h2>View Challans</h2>

        <!-- Filter Form (optional) -->
        <form method="get" action="viewchallans.php">
            <div class="form-row">
                <!-- Class Filter -->
                <div class="form-group col-md-3">
                    <label for="class-id">Class</label>
                    <select class="form-control" id="class-id" name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?= $cls['class_id'] ?>"
                                <?= ($class_filter == $cls['class_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cls['class_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section Filter -->
                <div class="form-group col-md-3">
                    <label for="section-id">Section</label>
                    <select class="form-control" id="section-id" name="section_id">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['section_id'] ?>"
                                <?= ($section_filter == $sec['section_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sec['section_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="form-group col-md-2" style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Challans Table -->
        <div class="row">
            <div class="col-12">
                <div class="card mt-3">
                    <div class="card-body">
                        <h4 class="card-title">Challan Records</h4>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Challan ID</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Month</th>
                                    <th>Session</th>
                                    <th>Total Fee</th>
                                    <th>Arrears</th>
                                    <th>Discount</th>
                                    <th>Final Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                        $challanID   = $row['challan_id'];
                                        $studentName = $row['student_name'];
                                        $className   = $row['class_name'];
                                        $sectionName = $row['section_name'];
                                        $month       = $row['challan_month'];
                                        $sessionVal  = $row['challan_session'];
                                        $totalFee    = $row['total_amount'];
                                        $arrears     = $row['arrears'];
                                        $discount    = $row['discount'];
                                        $finalAmt    = $row['final_amount'];
                                        $paid        = $row['amount_paid'];
                                        $status      = $row['challan_status'];
                                        ?>
                                        <tr>
                                            <td><?= $challanID ?></td>
                                            <td><?= htmlspecialchars($studentName) ?></td>
                                            <td><?= htmlspecialchars($className) ?></td>
                                            <td><?= htmlspecialchars($sectionName) ?></td>
                                            <td><?= htmlspecialchars($month) ?></td>
                                            <td><?= htmlspecialchars($sessionVal) ?></td>
                                            <td><?= number_format($totalFee, 2) ?></td>
                                            <td><?= number_format($arrears, 2) ?></td>
                                            <td><?= number_format($discount, 2) ?></td>
                                            <td><?= number_format($finalAmt, 2) ?></td>
                                            <td><?= number_format($paid, 2) ?></td>
                                            <td>
                                                <?php if ($status === 'paid'): ?>
                                                    <span class="badge badge-success">Paid</span>
                                                <?php elseif ($status === 'partially_paid'): ?>
                                                    <span class="badge badge-warning">Partially Paid</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Unpaid</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center">
                                            No Challans Found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div><!-- card-body -->
                </div><!-- card -->
            </div><!-- col -->
        </div><!-- row -->
    </div><!-- container-fluid -->
</div><!-- content-body -->

<?php
$conn->close();
include 'footer.php';  // Optional
?>