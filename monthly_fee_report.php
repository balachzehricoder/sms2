<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Sessions
$sessions = $conn->query("SELECT DISTINCT TRIM(challan_session) AS session_name FROM challans ORDER BY challan_session DESC");

// Define months for dropdown
$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// Get Filters
$selected_session = isset($_GET['session']) ? trim($_GET['session']) : '';
$selected_month = isset($_GET['month']) ? trim($_GET['month']) : '';

// Initialize summary data
$summary = [
    'previous_balance' => 0,
    'monthly_fee' => 0,
    'total_discount' => 0,
    'total_amount' => 0,
    'total_received' => 0,
    'total_receivables' => 0
];

// Fetch Fee Summary if session and month are selected
if (!empty($selected_session) && !empty($selected_month)) {
    $query = "SELECT 
                SUM(CASE WHEN status != 'paid' THEN arrears ELSE 0 END) AS previous_balance,
                SUM(total_amount) AS monthly_fee,
                SUM(discount) AS total_discount,
                SUM(final_amount) AS total_amount,
                SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) AS total_received,
                (SUM(final_amount) - SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END)) AS total_receivables
              FROM challans
              WHERE BINARY REPLACE(challan_month, ' ', '') = BINARY REPLACE('$selected_month', ' ', '')
              AND BINARY REPLACE(challan_session, ' ', '') = BINARY REPLACE('$selected_session', ' ', '')";

    $result = $conn->query($query);
    if ($result) {
        $summary = $result->fetch_assoc();
    }
}
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Monthly Fee Summary Report</h4>

        <!-- Selection Form -->
        <form method="GET" action="">
            <div class="row">
                <!-- Session Selection -->
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

                <!-- Month Selection -->
                <div class="col-md-3">
                    <label>Month</label>
                    <select name="month" class="form-control" required>
                        <option value="">Select Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= $month; ?>" <?= ($selected_month == $month) ? 'selected' : ''; ?>>
                                <?= $month; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <?php if (!empty($selected_session) && !empty($selected_month)): ?>
            <!-- Export and Print Buttons -->
            <div class="mt-4 text-right">
                <form method="POST" action="export_monthly_fee.php" class="d-inline-block">
                    <input type="hidden" name="session" value="<?= $selected_session; ?>">
                    <input type="hidden" name="month" value="<?= $selected_month; ?>">
                    <button type="submit" class="btn btn-success">Export to Excel</button>
                </form>
                <button onclick="printReport()" class="btn btn-secondary">Print Report</button>
            </div>

            <!-- Monthly Fee Summary Table -->
            <div class="mt-4" id="report-content">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Particulars</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><strong>Previous Balance</strong></td>
                            <td><?= number_format($summary['previous_balance'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><strong>Monthly Fee</strong></td>
                            <td><?= number_format($summary['monthly_fee'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><strong>Miscellaneous</strong></td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><strong>Transport</strong></td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><strong>Other Fee</strong></td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td><strong>Fine Received</strong></td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Total Discount</strong></td>
                            <td><?= number_format($summary['total_discount'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Total Amount</strong></td>
                            <td><?= number_format($summary['total_amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Total Received (of <?= htmlspecialchars($selected_month); ?> Challans)</strong></td>
                            <td><?= number_format($summary['total_received'], 2); ?></td>
                        </tr>
                        <tr class="bg-primary text-white">
                            <td colspan="2"><strong>Total Receivables</strong></td>
                            <td><?= number_format($summary['total_receivables'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function printReport() {
        let printContent = document.getElementById("report-content").innerHTML;
        let originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
    }
</script>

<?php require_once 'footer.php'; ?>