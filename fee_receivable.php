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

// Define months
$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// Get Filters
$selected_session = isset($_GET['session']) ? trim($_GET['session']) : '';
$selected_month = isset($_GET['month']) ? trim($_GET['month']) : '';
$selected_class = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';
$selected_section = isset($_GET['section_id']) ? trim($_GET['section_id']) : '';
$arrears_filter = isset($_GET['arrears_filter']) ? trim($_GET['arrears_filter']) : 'Above Amount';
$arrears_amount = isset($_GET['arrears_amount']) ? floatval($_GET['arrears_amount']) : 0;

// Build the WHERE clause
$where = "1";
if (!empty($selected_session)) $where .= " AND TRIM(challan_session) = TRIM('$selected_session')";
if (!empty($selected_month)) $where .= " AND TRIM(challan_month) = TRIM('$selected_month')";
if (!empty($selected_class)) $where .= " AND s.class_id = '$selected_class'";
if (!empty($selected_section)) $where .= " AND s.section_id = '$selected_section'";
if ($arrears_amount > 0) {
    $comparison = ($arrears_filter == 'Above Amount') ? ">=" : "<=";
    $where .= " AND c.arrears $comparison $arrears_amount";
}

// Fetch Fee Receivable Data
$query = "
    SELECT 
        c.challan_id, 
        c.challan_month, 
        c.challan_session, 
        c.total_amount, 
        c.arrears, 
        c.discount, 
        c.final_amount, 
        c.status, 
        s.student_id, 
        s.student_name, 
        s.family_code,
        cl.class_name, 
        sec.section_name,
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
?>

<style>
    /* Hide unnecessary elements when printing */
    @media print {
        body * {
            visibility: hidden;
        }

        #printTable,
        #printTable * {
            visibility: visible;
        }

        #printTable {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Fee Receivable Report</h4>

        <!-- Filters (Hidden in Print Mode) -->
        <form method="GET" action="" class="no-print">
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
                    <label>Challan Month</label>
                    <select name="month" class="form-control" required>
                        <option value="">Select Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= $month; ?>" <?= ($selected_month == $month) ? 'selected' : ''; ?>>
                                <?= $month; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Arrears Amount</label>
                    <select name="arrears_filter" class="form-control">
                        <option value="Above Amount" <?= ($arrears_filter == "Above Amount") ? "selected" : ""; ?>>Above Amount</option>
                        <option value="Below Amount" <?= ($arrears_filter == "Below Amount") ? "selected" : ""; ?>>Below Amount</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Enter Amount</label>
                    <input type="number" name="arrears_amount" class="form-control" value="<?= $arrears_amount; ?>" min="0">
                </div>

                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <!-- Buttons (Only visible on screen) -->
        <div class="no-print my-3">
            <button type="button" onclick="window.location.href='export_fee_receivable.php?session=<?= $selected_session ?>&month=<?= $selected_month ?>&class_id=<?= $selected_class ?>&section_id=<?= $selected_section ?>&arrears_filter=<?= $arrears_filter ?>&arrears_amount=<?= $arrears_amount ?>'" class="btn btn-success">
                Export to Excel
            </button>
            <button type="button" onclick="window.print()" class="btn btn-secondary">Print Report</button>
        </div>

        <!-- Fee Receivable Report Table -->
        <div class="mt-4">
            <table class="table table-bordered" id="printTable">
                <thead>
                    <tr>
                        <th>Challan ID</th>
                        <th>Student Name</th>
                        <th>Family Code</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Month</th>
                        <th>Total Fee</th>
                        <th>Arrears</th>
                        <th>Amount Paid</th>
                        <th>Final Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['challan_id']; ?></td>
                            <td><?= htmlspecialchars($row['student_name']); ?></td>
                            <td><?= htmlspecialchars($row['family_code']); ?></td>
                            <td><?= htmlspecialchars($row['class_name']); ?></td>
                            <td><?= htmlspecialchars($row['section_name']); ?></td>
                            <td><?= htmlspecialchars($row['challan_month']); ?></td>
                            <td><?= number_format($row['total_amount'], 2); ?></td>
                            <td><?= number_format($row['arrears'], 2); ?></td>
                            <td><?= number_format($row['amount_paid'], 2); ?></td>
                            <td><?= number_format($row['final_amount'], 2); ?></td>
                            <td><?= ucfirst($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function exportToExcel() {
        window.location.href = "export_fee_receivable.php";
    }
</script>

<?php require_once 'footer.php'; ?>