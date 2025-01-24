<?php
include 'confiq.php';  // Database connection
include 'header.php';  // Optional: Add header
include 'sidebar.php'; // Optional: Add sidebar

// Initialize variables
$family_code = '';
$students = [];
$total_arrears = 0;
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['family_code']) && isset($_POST['payable_amount'])) {
        $family_code = $_POST['family_code'];
        $payable_amount = $_POST['payable_amount'];

        foreach ($payable_amount as $student_id => $amount) {
            $amount = (float)$amount;

            if ($amount > 0) {
                // Insert payment into the database
                $payment_query = "
                    INSERT INTO payments (student_id, payment_date, payment_amount)
                    VALUES ('$student_id', NOW(), '$amount')
                ";
                $conn->query($payment_query);
            }
        }

        $message = "Payments recorded successfully.";
    } else {
        $message = "Error: Invalid input.";
    }
}

// Handle family code submission for fetching students
if (isset($_POST['family_code']) && !isset($_POST['payable_amount'])) {
    $family_code = $_POST['family_code'];

    // Fetch students with the matching family code
    $query = "
        SELECT 
            students.student_id, 
            students.student_name, 
            classes.class_name, 
            classes.standard_monthly_fee, 
            COALESCE(SUM(payments.payment_amount), 0) AS total_paid,
            (classes.standard_monthly_fee - COALESCE(SUM(payments.payment_amount), 0)) AS arrears
        FROM students
        JOIN classes ON students.class_id = classes.class_id
        LEFT JOIN payments ON students.student_id = payments.student_id
        WHERE students.family_code = '$family_code'
        GROUP BY students.student_id
    ";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
            $total_arrears += max(0, $row['arrears']);
        }
    } else {
        $message = "No students found with the given family code.";
    }
}
?>

<div class="content-body">
    <div class="container-fluid">
        <h2>Pay Fees</h2>

        <!-- Family Code Form -->
        <form method="post" action="">
            <div class="form-group">
                <label for="family-code">Family Code</label>
                <input type="text" name="family_code" id="family-code" class="form-control" value="<?= htmlspecialchars($family_code) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Display message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Results -->
        <?php if (!empty($students)): ?>
            <h3 class="mt-4">Students List</h3>
            <form method="post" action="">
                <input type="hidden" name="family_code" value="<?= htmlspecialchars($family_code) ?>">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Fee Amount</th>
                            <th>Total Paid</th>
                            <th>Arrears</th>
                            <th>Payable Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student['student_name']) ?></td>
                                <td><?= htmlspecialchars($student['class_name']) ?></td>
                                <td><?= number_format($student['standard_monthly_fee'], 2) ?></td>
                                <td><?= number_format($student['total_paid'], 2) ?></td>
                                <td><?= number_format(max(0, $student['arrears']), 2) ?></td>
                                <td>
                                    <input 
                                        type="number" 
                                        name="payable_amount[<?= $student['student_id'] ?>]" 
                                        class="form-control" 
                                        min="0" 
                                        max="<?= max(0, $student['arrears']) ?>" 
                                        step="0.01" 
                                        value="0"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success">Submit Payments</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
include 'footer.php';  // Optional: Add footer
?>
