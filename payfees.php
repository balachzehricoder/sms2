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
 */
function payStudentArrears($conn, $student_id, $paymentAmount)
{
    $unpaidQuery = "
        SELECT
    ch.challan_id,
    ch.final_amount,
    (
      ch.final_amount - COALESCE((
          SELECT SUM(DISTINCT p.amount_paid) 
          FROM payments p 
          WHERE p.challan_id = ch.challan_id
      ), 0)
    ) AS outstanding_balance
FROM challans ch
WHERE ch.student_id = ? AND ch.status != 'paid'
ORDER BY ch.challan_date ASC, ch.challan_id ASC;

    ";
    
    // Use prepared statements
    $stmt = $conn->prepare($unpaidQuery);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $unpaidResult = $stmt->get_result();
    
    $remaining = $paymentAmount;

    while ($unpaidResult && $unpaidResult->num_rows > 0 && $remaining > 0 && ($row = $unpaidResult->fetch_assoc())) {
        $challan_id  = $row['challan_id'];
        $final_amount = (float)$row['final_amount'];
        $outstanding = (float)$row['outstanding_balance'];

        if ($outstanding <= 0) {
            continue;
        }

        $toPay = min($outstanding, $remaining);

        // Insert payment record using prepared statement
        $paymentInsert = $conn->prepare("
            INSERT INTO payments (challan_id, payment_date, amount_paid, created_at)
            VALUES (?, NOW(), ?, NOW())
        ");
        $paymentInsert->bind_param("id", $challan_id, $toPay);
        $paymentInsert->execute();

        $remaining -= $toPay;

        // Check how much has now been paid for this challan
        $paidCheckQuery = $conn->prepare("
            SELECT SUM(amount_paid) AS total_paid
            FROM payments
            WHERE challan_id = ?
        ");
        $paidCheckQuery->bind_param("i", $challan_id);
        $paidCheckQuery->execute();
        $paidCheckRes = $paidCheckQuery->get_result();
        $totalPaid = 0;

        if ($paidCheckRes && $paidCheckRes->num_rows > 0) {
            $totalPaid = (float)$paidCheckRes->fetch_assoc()['total_paid'];
        }

        // Update challan status
        if ($totalPaid >= $final_amount) {
            $updateStmt = $conn->prepare("UPDATE challans SET status = 'paid' WHERE challan_id = ?");
        } else {
            $updateStmt = $conn->prepare("UPDATE challans SET status = 'partially_paid' WHERE challan_id = ?");
        }
        $updateStmt->bind_param("i", $challan_id);
        $updateStmt->execute();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['family_code']) && !isset($_POST['payable_amount'])) {
        $family_code = mysqli_real_escape_string($conn, $_POST['family_code']);

        $query = "
   SELECT
    s.student_id,
    s.student_name,
    c.class_name,
    COALESCE(ch.total_fee, 0) AS total_fee,
    COALESCE(p.total_paid, 0) AS total_paid,
    (COALESCE(ch.total_fee, 0) - COALESCE(p.total_paid, 0)) AS arrears
FROM students s
JOIN classes c ON s.class_id = c.class_id
LEFT JOIN (
    -- Get total unpaid challans fee per student
    SELECT
        ch.student_id,
        SUM(ch.final_amount) AS total_fee
    FROM challans ch
    WHERE ch.status != 'paid'  
    GROUP BY ch.student_id
) ch ON s.student_id = ch.student_id
LEFT JOIN (
    -- Correctly sum payments for unpaid challans
    SELECT
        ch2.student_id,
        SUM(p2.amount_paid) AS total_paid 
    FROM payments p2
    JOIN challans ch2 ON p2.challan_id = ch2.challan_id
    WHERE ch2.status != 'paid'  
    GROUP BY ch2.student_id
) p ON s.student_id = p.student_id
WHERE s.family_code = ?
GROUP BY s.student_id, s.student_name, c.class_name, ch.total_fee, p.total_paid;



        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $family_code);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $row['arrears'] = max(0, (float)$row['arrears']);
                $students[] = $row;
                $total_arrears += $row['arrears'];
            }
        } else {
            $message = "No students found for the given family code.";
        }
    } elseif (!empty($_POST['family_code']) && !empty($_POST['payable_amount'])) {
        $family_code = mysqli_real_escape_string($conn, $_POST['family_code']);
        $payable_amounts = $_POST['payable_amount'];

        foreach ($payable_amounts as $student_id => $amt) {
            $amt = (float)$amt;
            if ($amt > 0) {
                payStudentArrears($conn, $student_id, $amt);
            }
        }

        $message = "Payments recorded successfully.";
    }
}
?>

<div class="content-body">
    <div class="container-fluid">
        <h2>Pay Fees</h2>

        <form method="post">
            <label for="family-code">Family Code</label>
            <input type="text" name="family_code" id="family-code" class="form-control" value="<?= htmlspecialchars($family_code) ?>" required>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (!empty($students)): ?>
            <h3 class="mt-4">Students List</h3>
            <form method="post">
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
                                <td><input type="text" name="payable_amount[<?= $stu['student_id'] ?>]" class="form-control" max="<?= $stu['arrears'] ?>" value="<?= $stu['arrears'] ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success">Submit Payments</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php' ?>