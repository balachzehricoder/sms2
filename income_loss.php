<?php
require_once 'confiq.php'; // Database connection
require_once 'header.php';  // Header with navbar
require_once 'sidebar.php'; // Sidebar (Will be hidden in print view)

// Get Date Filters (Handle empty or default dates)
$from_date = isset($_GET['from_date']) ? mysqli_real_escape_string($conn, trim($_GET['from_date'])) : '2025-01-01';
$to_date = isset($_GET['to_date']) ? mysqli_real_escape_string($conn, trim($_GET['to_date'])) : '2025-12-31';

// Initialize WHERE clauses for SQL queries
$where_fee = "1";
$where_expense = "1";

// Apply date filters if provided
if (!empty($from_date) && !empty($to_date)) {
    $where_fee .= " AND p.payment_date BETWEEN '$from_date' AND '$to_date'";
    $where_expense .= " AND e.expense_date BETWEEN '$from_date' AND '$to_date'";
}

// Fetch Fee Collection Data (Income)
$query_fee = "
    SELECT 
        p.payment_date, 
        s.student_name, 
        s.family_code, 
        cl.class_name, 
        sec.section_name, 
        c.challan_month, 
        c.total_amount, 
        c.discount, 
        c.final_amount, 
        COALESCE(SUM(p.amount_paid), 0) AS amount_paid
    FROM payments p
    JOIN challans c ON p.challan_id = c.challan_id
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE $where_fee
    GROUP BY p.payment_date, s.student_id
    ORDER BY p.payment_date ASC";

$result_fee = $conn->query($query_fee);

// Fetch Expenses Data
$query_expense = "
    SELECT 
        e.expense_date, 
        ec.category_name AS category,  
        e.description, 
        e.amount
    FROM expenses e
    JOIN expense_categories ec ON e.category_id = ec.category_id 
    WHERE $where_expense
    ORDER BY e.expense_date ASC";

$result_expense = $conn->query($query_expense);

// Fetch Summary
$summary_query = "
    SELECT 
        (SELECT COALESCE(SUM(p.amount_paid), 0) FROM payments p JOIN challans c ON p.challan_id = c.challan_id WHERE $where_fee) AS total_income,
        (SELECT COALESCE(SUM(e.amount), 0) FROM expenses e WHERE $where_expense) AS total_expense";

$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();
$total_income = isset($summary['total_income']) ? $summary['total_income'] : 0;
$total_expense = isset($summary['total_expense']) ? $summary['total_expense'] : 0;
$total_balance = $total_income - $total_expense;
?>

<!-- HTML to display report -->
<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Detailed Income & Expense Report</h4>

        <!-- Filters -->
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $from_date; ?>">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $to_date; ?>">
                </div>
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary no-print">Search</button>
                </div>
            </div>
        </form>

        <!-- Export & Print Buttons -->
        <?php if (!empty($from_date) && !empty($to_date)): ?>
            <div class="mt-3">
                <!-- Export to PDF link -->
                <!-- <a href="export_income_expense.php?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>" class="btn btn-success no-print">Export to PDF</a> -->
                <button onclick="window.print()" class="btn btn-secondary no-print">Print Report</button>
            </div>
        <?php endif; ?>

        <!-- Income Table -->
        <h4 class="mt-4">Detailed Fee Collection (Income)</h4>
        <div class="mt-2" id="income-report-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student Name</th>
                        <th>Family Code</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Month</th>
                        <th>Total Fee</th>
                        <th>Discount</th>
                        <th>Final Amount</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_fee->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['payment_date']); ?></td>
                            <td><?= htmlspecialchars($row['student_name']); ?></td>
                            <td><?= htmlspecialchars($row['family_code']); ?></td>
                            <td><?= htmlspecialchars($row['class_name']); ?></td>
                            <td><?= htmlspecialchars($row['section_name']); ?></td>
                            <td><?= htmlspecialchars($row['challan_month']); ?></td>
                            <td><?= number_format($row['total_amount'], 2); ?></td>
                            <td><?= number_format($row['discount'], 2); ?></td>
                            <td><?= number_format($row['final_amount'], 2); ?></td>
                            <td><?= number_format($row['amount_paid'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Expense Table -->
        <h4 class="mt-4">Detailed Expenses</h4>
        <div class="mt-2" id="expense-report-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_expense->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['expense_date']); ?></td>
                            <td><?= htmlspecialchars($row['category']); ?></td>
                            <td><?= htmlspecialchars($row['description']); ?></td>
                            <td><?= number_format($row['amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <h4 class="mt-4">Summary</h4>
        <div class="mt-2">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Total Income</th>
                        <td><?= number_format($total_income, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Total Expenses</th>
                        <td><?= number_format($total_expense, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Net Balance</th>
                        <td><strong><?= number_format($total_balance, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function printReport() {
        window.print(); // Trigger the print dialog
    }
</script>

<!-- Print-specific CSS to hide unwanted elements -->
<style>
    @media print {

        /* Hide all elements except the report content */
        .no-print,
        .sidebar,
        .header,
        .footer {
            display: none !important;
        }

        /* Hide everything else except the report */
        body * {
            visibility: hidden;
        }

        #income-report-content,
        #income-report-content * {
            visibility: visible;
        }

        #expense-report-content,
        #expense-report-content * {
            visibility: visible;
        }

        #income-report-content,
        #expense-report-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>

<?php require_once 'footer.php'; ?>