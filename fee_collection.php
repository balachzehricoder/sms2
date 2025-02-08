<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Sessions
$sessions = $conn->query("SELECT DISTINCT TRIM(challan_session) AS session_name FROM challans ORDER BY challan_session DESC");

// Fetch Classes
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");

// Fetch Sections
$sections = $conn->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC");

// Get Filters
$selected_session = isset($_GET['session']) ? trim($_GET['session']) : '';
$selected_class = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';
$selected_section = isset($_GET['section_id']) ? trim($_GET['section_id']) : '';
$from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

// Build the WHERE clause
$where = "1"; // Always true condition
if (!empty($selected_session)) $where .= " AND TRIM(c.challan_session) = TRIM('$selected_session')";
if (!empty($selected_class)) $where .= " AND s.class_id = '$selected_class'";
if (!empty($selected_section)) $where .= " AND s.section_id = '$selected_section'";
if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND p.payment_date BETWEEN '$from_date' AND '$to_date'";
}

// Fetch Fee Collection Data
$query = "
    SELECT 
        c.challan_id, 
        s.student_id, 
        s.student_name, 
        s.family_code, 
        cl.class_name, 
        sec.section_name, 
        c.challan_month,
        c.due_date,
        c.total_amount, 
        c.arrears, 
        c.discount, 
        c.final_amount, 
        c.status, 
        COALESCE(SUM(p.amount_paid), 0) AS amount_paid
    FROM challans c
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    LEFT JOIN payments p ON p.challan_id = c.challan_id
    WHERE $where
    GROUP BY c.challan_id
    ORDER BY c.challan_id DESC";

$result = $conn->query($query);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

// Calculate Summary Data
$summary_query = "
    SELECT 
        SUM(c.total_amount) AS total_fees,
        SUM(c.discount) AS total_discounts,
        SUM(c.arrears) AS total_arrears,
        SUM(p.amount_paid) AS total_collected,
        (SUM(c.final_amount) - SUM(p.amount_paid)) AS total_remaining,
        COUNT(DISTINCT CASE WHEN c.status = 'paid' THEN c.student_id END) AS total_paid_students,
        COUNT(DISTINCT CASE WHEN c.status != 'paid' THEN c.student_id END) AS total_unpaid_students
    FROM challans c
    LEFT JOIN payments p ON p.challan_id = c.challan_id
    WHERE $where";

$summary_result = $conn->query($summary_query);
$summary = ($summary_result) ? $summary_result->fetch_assoc() : [];
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Fee Collection Report</h4>

        <!-- Filters -->
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-3">
                    <label>Session</label>
                    <select name="session" class="form-control" required>
                        <option value="">Select Session</option>
                        <?php while ($session = $sessions->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($session['session_name']); ?>" <?= ($selected_session == $session['session_name']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($session['session_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Class</label>
                    <select name="class_id" class="form-control">
                        <option value="">All</option>
                        <?php while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id']; ?>" <?= ($selected_class == $class['class_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Section</label>
                    <select name="section_id" class="form-control">
                        <option value="">All</option>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <option value="<?= $section['section_id']; ?>" <?= ($selected_section == $section['section_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($section['section_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $from_date; ?>">
                </div>

                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $to_date; ?>">
                </div>

                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <!-- Export & Print Buttons -->
        <?php if (!empty($selected_session)): ?>
            <div class="mt-3">
                <a href="export_fee_collection.php?session=<?= $selected_session ?>&class_id=<?= $selected_class ?>&section_id=<?= $selected_section ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>" class="btn btn-success">Export to Excel</a>
                <button onclick="printReport()" class="btn btn-secondary no-print">Print Report</button>
            </div>
        <?php endif; ?>
        <!-- Export & Print Buttons -->
        <div class="mt-3">
            <a href="export_fee_collection.php?session=<?= $selected_session ?>&class_id=<?= $selected_class ?>&section_id=<?= $selected_section ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>" class="btn btn-success">Export to Excel</a>
            <button onclick="printReport()" class="btn btn-secondary no-print">Print Report</button>
        </div>
        <!-- Table -->
        <div class="mt-4" id="printable-section">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Student Name</th>
                        <th>Family Code</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Month</th>
                        <th>Due Date</th>
                        <th>Total Fee</th>
                        <th>Arrears</th>
                        <th>Discount</th>
                        <th>Final Amount</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $serial = 1;
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $serial++; ?></td>
                            <td><?= htmlspecialchars($row['student_name']); ?></td>
                            <td><?= htmlspecialchars($row['family_code']); ?></td>
                            <td><?= htmlspecialchars($row['class_name']); ?></td>
                            <td><?= htmlspecialchars($row['section_name']); ?></td>
                            <td><?= htmlspecialchars($row['challan_month']); ?></td>
                            <td><?= htmlspecialchars($row['due_date']); ?></td>
                            <td><?= number_format($row['total_amount'], 2); ?></td>
                            <td><?= number_format($row['arrears'], 2); ?></td>
                            <td><?= number_format($row['discount'], 2); ?></td>
                            <td><?= number_format($row['final_amount'], 2); ?></td>
                            <td><?= number_format($row['amount_paid'], 2); ?></td>
                            <td><?= ucfirst($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Bottom Summary Section -->
        <div class="mt-4">
            <h5><strong>Total Paid Students:</strong> <?= $summary['total_paid_students']; ?></h5>
            <h5><strong>Total Unpaid Students:</strong> <?= $summary['total_unpaid_students']; ?></h5>
            <h5><strong>Total Fees:</strong> <?= number_format($summary['total_fees'], 2); ?></h5>
            <h5><strong>Total Discounts:</strong> <?= number_format($summary['total_discounts'], 2); ?></h5>
            <h5><strong>Total Arrears:</strong> <?= number_format($summary['total_arrears'], 2); ?></h5>
        </div>
    </div>
</div>

<style>
    @media print {

        /* Hide Unnecessary Sections */
        body * {
            visibility: hidden;
        }

        /* Show Only the Printable Content */
        #printable-section,
        #printable-section * {
            visibility: visible;
        }

        /* Hide Navigation, Sidebar, Footer, and Filters */
        .no-print,
        .sidebar,
        .header,
        .footer,
        form,
        .btn,
        .card-title {
            display: none !important;
        }

        /* Adjust Page Margins */
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: auto;
        }

        /* Table Formatting for Print */
        #printable-section {
            width: 100%;
            margin: 0 auto;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            /* Adjust font size if needed */
        }

        table th,
        table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        /* Summary Section Formatting */
        #summary-section {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            visibility: visible;
        }

        /* Avoid page breaks inside table rows */
        tr {
            page-break-inside: avoid;
        }
    }
</style>

<script>
    function printReport() {
        window.print();
    }
</script>


<?php require_once 'footer.php'; ?>