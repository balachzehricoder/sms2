<?php
include 'confiq.php';
// include 'header.php';
// include 'sidebar.php';

// Only proceed if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Grab form inputs
    $session          = mysqli_real_escape_string($conn, $_POST['session']);
    $challan_month    = mysqli_real_escape_string($conn, $_POST['challan_month']);
    $class_id         = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_id       = isset($_POST['section_id']) ? mysqli_real_escape_string($conn, $_POST['section_id']) : '';
    $due_date         = mysqli_real_escape_string($conn, $_POST['due_date']);
    $include_discounts = mysqli_real_escape_string($conn, $_POST['include_discounts']);
    $custom_fee       = !empty($_POST['default_fee']) ? (float)$_POST['default_fee'] : null;
    $single_student_id = !empty($_POST['single_student_id']) ? (int)$_POST['single_student_id'] : 0;

    // 2) Build a query to find the target students
    if ($single_student_id > 0) {
        // Single student only
        $students_query = "
            SELECT 
                s.student_id,
                s.monthly_fee,
                COALESCE(sd.discount_amount, 0) AS discount_amount,
                c.standard_monthly_fee
            FROM students s
            JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN student_discounts sd ON s.student_id = sd.student_id
            WHERE s.student_id = '$single_student_id'
            LIMIT 1
        ";
    } else {
        // All in class/section
        $students_query = "
            SELECT 
                s.student_id,
                s.monthly_fee,
                COALESCE(sd.discount_amount, 0) AS discount_amount,
                c.standard_monthly_fee
            FROM students s
            JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN student_discounts sd ON s.student_id = sd.student_id
            WHERE s.class_id = '$class_id'
        ";
        if (!empty($section_id)) {
            $students_query .= " AND s.section_id = '$section_id'";
        }
    }

    $students_result = $conn->query($students_query);

    if ($students_result && $students_result->num_rows > 0) {
        $countChallans = 0;

        while ($stu = $students_result->fetch_assoc()) {
            $student_id       = $stu['student_id'];
            $baseMonthlyFee   = ($custom_fee !== null)
                ? $custom_fee
                : (($stu['monthly_fee'] > 0)
                    ? $stu['monthly_fee']
                    : $stu['standard_monthly_fee']);

            $student_discount = ($include_discounts === 'yes')
                ? (float)$stu['discount_amount']
                : 0.0;

            // 3) Calculate total unpaid from previous challans => arrears
            //    Summing all challans that are not 'paid'
            $arrears_query = "
         SELECT 
    SUM((ch.total_amount - ch.discount) 
        - COALESCE((SELECT SUM(p.amount_paid) 
                    FROM payments p 
                    WHERE p.challan_id = ch.challan_id), 0)
    ) AS total_unpaid
FROM challans ch
WHERE ch.student_id = '$student_id'
AND ch.status != 'paid';  -- ✅ Exclude fully paid challans


        ";
        
        $arrears_res = $conn->query($arrears_query);
        $arrears = 0.00;
        if ($arrears_res && $row = $arrears_res->fetch_assoc()) {
            $arrears = (float)$row['total_unpaid'];
        }
        if ($arrears < 0) {
            $arrears = 0; // No negative arrears
        }
        

            // 4) final_amount = baseMonthlyFee + arrears - discount
            $final_amount = $baseMonthlyFee + $arrears - $student_discount;
            if ($final_amount < 0) {
                $final_amount = 0;
            }

            // 5) Insert into challans
            $insert_challan = "
                INSERT INTO challans (
                    student_id,
                    challan_date,
                    due_date,
                    challan_month,
                    challan_session,
                    total_amount,
                    arrears,
                    discount,
                    final_amount,
                    status,
                    created_at
                ) VALUES (
                    '$student_id',
                    NOW(),
                    '$due_date',
                    '$challan_month',
                    '$session',
                    '$baseMonthlyFee',
                    '$arrears',
                    '$student_discount',
                    '$final_amount',
                    'unpaid',
                    NOW()
                )
            ";
            if ($conn->query($insert_challan)) {
                $countChallans++;
            } else {
                echo "Error creating challan for student_id {$student_id}: " . $conn->error . "<br>";
            }
        }

        // Done creating challans
        echo "<script>
            alert('Successfully created $countChallans challan(s).');
            window.location.href = 'viewchallan'; 
        </script>";
        exit;
    } else {
        echo "<script>
            alert('No matching students found.');
            window.location.href = 'challan';
        </script>";
        exit;
    }
}

$conn->close();
// include 'footer.php';
