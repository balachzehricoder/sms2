<?php
include 'confiq.php';   // Database connection
include 'header.php';   // Optional
include 'sidebar.php';  // Optional

// Initialize variables
$family_code = '';
$students = [];
$total_arrears = 0;
$message = "";

/**
 * Distribute a payment amount across all unpaid challans (oldest first) for a single student.
 * - Pull all unpaid/partially paid challans in ascending order by date.
 * - For each challan, figure out how much is still owed.
 * - Pay as much as we can (or as much as remains in $paymentAmount).
 * - Update the 'payments' table & challan status accordingly.
 */
function payStudentArrears($conn, $student_id, $paymentAmount)
{
    // 1) Get all challans for this student that are NOT 'paid', oldest first
    $unpaidQuery = "
        SELECT
            ch.challan_id,
            ch.final_amount,
            (
              ch.final_amount 
              - COALESCE(
                  (SELECT SUM(p.amount_paid) 
                   FROM payments p 
                   WHERE p.challan_id = ch.challan_id),
                   0
                )
            ) AS outstanding_balance
        FROM challans ch
        WHERE ch.student_id = '$student_id'
          AND ch.status != 'paid'
        ORDER BY ch.challan_date ASC, ch.challan_id ASC
    ";
    $unpaidResult = $conn->query($unpaidQuery);

    $remaining = $paymentAmount; // how much the user wants to pay

    // 2) Go through each unpaid challan in ascending order
    while ($unpaidResult && $unpaidResult->num_rows > 0 && $remaining > 0 && ($row = $unpaidResult->fetch_assoc())) {
        $challan_id        = $row['challan_id'];
        $final_amount      = (float)$row['final_amount'];
        $outstanding       = (float)$row['outstanding_balance'];

        if ($outstanding <= 0) {
            // This challan is effectively paid, skip it
            continue;
        }

        // 3) Pay either the entire outstanding or whatever remains
        $toPay = min($outstanding, $remaining);

        // 4) Insert a payment record
        $paymentInsert = "
            INSERT INTO payments (challan_id, payment_date, amount_paid, created_at)
            VALUES ('$challan_id', NOW(), '$toPay', NOW())
        ";
        $conn->query($paymentInsert);

        // 5) Subtract from the amount the user intended to pay
        $remaining -= $toPay;

        // 6) Check how much has now been paid for this challan in total
        $paidCheckQuery = "
            SELECT SUM(amount_paid) AS total_paid
            FROM payments
            WHERE challan_id = '$challan_id'
        ";
        $paidCheckRes = $conn->query($paidCheckQuery);
        $totalPaid    = 0;
        if ($paidCheckRes && $paidCheckRes->num_rows > 0) {
            $totalPaid = (float)$paidCheckRes->fetch_assoc()['total_paid'];
        }

        // 7) Update challan status based on new total paid
        if ($totalPaid >= $final_amount) {
            // fully paid
            $conn->query("UPDATE challans SET status = 'paid' WHERE challan_id = '$challan_id'");
        } elseif ($totalPaid > 0) {
            $conn->query("UPDATE challans SET status = 'partially_paid' WHERE challan_id = '$challan_id'");
        }
        // else remain 'unpaid'
    }

    // If there's leftover (remaining > 0) after paying all challans,
    // you could record it as a "prepayment" or ignore it. Here we ignore it.
    return $remaining;
}

// 0) Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CASE A: Searching by Family Code (no payment yet)
    if (isset($_POST['family_code']) && !isset($_POST['payable_amount'])) {
        $family_code = mysqli_real_escape_string($conn, $_POST['family_code']);

        // Query to fetch each student's total fee, total paid, and arrears
        // We'll sum across all challans for each student
        $query = "
            SELECT
                s.student_id,
                s.student_name,
                c.class_name,
                
                -- We'll compute 'total_fee' and 'total_paid' by adding up across all challans
                COALESCE(SUM(ch.final_amount), 0) AS total_fee,
                
                -- sum of all payments for all challans of that student
                COALESCE((
                    SELECT SUM(p2.amount_paid)
                    FROM payments p2
                    JOIN challans ch2 ON ch2.challan_id = p2.challan_id
                    WHERE ch2.student_id = s.student_id
                ), 0) AS total_paid,
                
                -- arrears = total fee - total paid
                (
                    COALESCE(SUM(ch.final_amount), 0) 
                    - COALESCE((
                          SELECT SUM(p2.amount_paid) 
                          FROM payments p2
                          JOIN challans ch2 ON ch2.challan_id = p2.challan_id
                          WHERE ch2.student_id = s.student_id
                      ), 0)
                ) AS arrears
                
            FROM students s
            JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN challans ch ON ch.student_id = s.student_id
            WHERE s.family_code = '$family_code'
            GROUP BY s.student_id
        ";
        $res = $conn->query($query);

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                // Only keep positive or zero for arrears
                $row['arrears'] = max(0, (float)$row['arrears']);
                $students[] = $row;
                $total_arrears += $row['arrears'];
            }
            if (empty($students)) {
                $message = "No students or arrears found for that family code.";
            }
        } else {
            $message = "No students found for the given family code.";
        }
    }
    // CASE B: Submitting Payment
    elseif (isset($_POST['family_code']) && isset($_POST['payable_amount'])) {
        $family_code = mysqli_real_escape_string($conn, $_POST['family_code']);
        $payable_amounts = $_POST['payable_amount'];

        // For each student, attempt to pay their arrears
        foreach ($payable_amounts as $student_id => $amt) {
            $amt = (float)$amt;
            if ($amt > 0) {
                payStudentArrears($conn, $student_id, $amt);
            }
        }

        $message = "Payments recorded successfully.";

        // Re-fetch updated data so we see new totals
        $query = "
            SELECT
                s.student_id,
                s.student_name,
                c.class_name,
                COALESCE(SUM(ch.final_amount), 0) AS total_fee,
                COALESCE((
                    SELECT SUM(p2.amount_paid)
                    FROM payments p2
                    JOIN challans ch2 ON ch2.challan_id = p2.challan_id
                    WHERE ch2.student_id = s.student_id
                ), 0) AS total_paid,
                (
                    COALESCE(SUM(ch.final_amount), 0) 
                    - COALESCE((
                        SELECT SUM(p2.amount_paid)
                        FROM payments p2
                        JOIN challans ch2 ON ch2.challan_id = p2.challan_id
                        WHERE ch2.student_id = s.student_id
                    ), 0)
                ) AS arrears
            FROM students s
            JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN challans ch ON ch.student_id = s.student_id
            WHERE s.family_code = '$family_code'
            GROUP BY s.student_id
        ";
        $res = $conn->query($query);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $row['arrears'] = max(0, (float)$row['arrears']);
                $students[] = $row;
                $total_arrears += $row['arrears'];
            }
        }
    }
}
?>

<div class="content-body">
    <div class="container-fluid">
        <h2>Pay Fees</h2>

        <!-- 1. Family Code Form -->
        <form method="post" action="">
            <div class="form-group">
                <label for="family-code">Family Code</label>
                <input
                    type="text"
                    name="family_code"
                    id="family-code"
                    class="form-control"
                    value="<?= htmlspecialchars($family_code) ?>"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- 2. Display any message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- 3. If we found any students, show them + payment form -->
        <?php if (!empty($students)): ?>
            <h3 class="mt-4">Students List</h3>
            <form method="post" action="">
                <!-- Keep the family code hidden so we know which family again -->
                <input type="hidden" name="family_code" value="<?= htmlspecialchars($family_code) ?>">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Total Fee</th>
                            <th>Total Paid</th>
                            <th>Arrears</th>
                            <th>Payable Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $stu): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($stu['student_name']) ?></td>
                                <td><?= htmlspecialchars($stu['class_name']) ?></td>
                                <td><?= number_format($stu['total_fee'], 2) ?></td>
                                <td><?= number_format($stu['total_paid'], 2) ?></td>
                                <td><?= number_format($stu['arrears'], 2) ?></td>
                                <td>
                                    <input
                                        type="number"
                                        name="payable_amount[<?= $stu['student_id'] ?>]"
                                        class="form-control"
                                        min="0"
                                        step="0.01"
                                        max="<?= $stu['arrears'] ?>"
                                        value="<?= $stu['arrears'] ?>">
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
include 'footer.php';  // Optional
?>