<?php
include 'confiq.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get class ID from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id === 0) {
    echo "<div class='alert alert-danger'>Invalid Class ID.</div>";
    exit;
}

// Fetch class details
$classQuery = "SELECT class_name FROM classes WHERE class_id = ?";
$stmt = $conn->prepare($classQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classDetails = $result->fetch_assoc();

if (!$classDetails) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    exit;
}

// Fetch students in the class
$studentQuery = "SELECT 
                    student_id,
                    family_code,
                    student_name,
                    gr_no,
                    gender,
                    father_name,
                    father_cell_no,
                    dob,
                    date_of_admission,
                    status,
                    session_name,
                    religion,
                    student_image,
                    monthly_fee
                FROM students 
                JOIN sessions ON students.session = sessions.id
                WHERE status = 'active' AND class_id = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$studentsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Class Details</title>
    <link href="./plugins/tables/css/datatable/dataTables.bootstrap4.min.css" rel="stylesheet">

        <!-- Custom Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
    <link href="./css/custome.css" rel="stylesheet">
    <style>
       body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 900px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background: #f4f4f4;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn:hover {
            opacity: 0.9;
        }

        /* Print Specific Styling */
        @media print {
            .no-print {
                display: none;
            }
            .container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            body {
                margin: 0;
            }
        }

        /* Style for images */
        .student-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h3>Class Details: <?php echo htmlspecialchars($classDetails['class_name']); ?></h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Student ID</th>
                    <th>Family Code</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Father's Name</th>
                    <th>Father's Cell No</th>
                    <th>Date of Birth</th>
                    <th>Date of Admission</th>
                    <th>Session</th>
                    <th>Religion</th>
                    <th>Monthly Fee</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($studentsResult->num_rows > 0) {
                    $sno = 1;
                    while ($student = $studentsResult->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $sno++ . "</td>
                                <td>" . htmlspecialchars($student['student_id']) . "</td>
                                <td>" . htmlspecialchars($student['family_code']) . "</td>
                                <td>" . htmlspecialchars($student['student_name']) . "</td>
                                <td>" . htmlspecialchars($student['gender']) . "</td>
                                <td>" . htmlspecialchars($student['father_name']) . "</td>
                                <td>" . htmlspecialchars($student['father_cell_no']) . "</td>
                                <td>" . htmlspecialchars($student['dob']) . "</td>
                                <td>" . htmlspecialchars($student['date_of_admission']) . "</td>
                                <td>" . htmlspecialchars($student['session_name']) . "</td>
                                <td>" . htmlspecialchars($student['religion']) . "</td>
                                <td>" . htmlspecialchars($student['monthly_fee']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='13'>No students found in this class.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <button class="btn btn-primary no-print" onclick="window.print();">Print</button>
        <a href="view_class.php?class_id=<?php echo $class_id; ?>" class="btn btn-secondary no-print">Back</a>
    </div>
</body>
</html>
