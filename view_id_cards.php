<?php
include 'confiq.php'; // Include database configuration
include 'header.php'; // Include the header
include 'sidebar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get filter parameters
$class_id = isset($_GET['class']) ? $_GET['class'] : '';
$session_id = isset($_GET['session']) ? $_GET['session'] : '';

// Validate inputs
if (empty($class_id) || empty($session_id)) {
    echo "<p class='text-danger'>Invalid filters. Please go back and select a Class and Session.</p>";
    exit();
}

// Fetch students based on filters
$query = "SELECT 
            students.student_id,
            students.student_name,
            students.father_name,
            students.family_code,
            classes.class_name,
            sessions.session_name,
            students.gr_no,
            sections.section_name
          FROM students
          LEFT JOIN classes ON students.class_id = classes.class_id
          LEFT JOIN sessions ON students.session = sessions.id
          LEFT JOIN sections ON students.section_id = sections.section_id
          WHERE students.class_id = '$class_id' 
            AND students.session = '$session_id' 
            AND students.status = 'active'";


$result = $conn->query($query);

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<style>
    .id-card-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        padding: 20px;
        gap: 20px;
    }

    .id-card {
        border: 1px solid #000;
        padding: 15px;
        text-align: center;
        background: #fff;
        width: 300px;
        height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }

    .id-card h3 {
        margin: 0;
        font-size: 18px;
        color: #333;
    }

    .id-card p {
        margin: 5px 0;
        font-size: 14px;
        color: #555;
    }

    .print-btn {
        text-align: center;
        margin: 20px;
    }

    .btn-success {
        padding: 10px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .capitalized {
        text-transform: capitalize;
    }

    /* Hide header, sidebar, and footer during printing */
    @media print {

        .header,
        .nk-sidebar,
        .footer,
        .print-btn {
            display: none !important;
            /* Completely remove these elements during printing */
        }

        .id-card-container {
            margin: 0;
            padding: 0;
        }

        .id-card {
            page-break-inside: avoid;
            /* Ensure cards do not break across pages */
        }
    }
</style>

<div class="container mt-5">
    <div class="id-card-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="id-card mt-5 h-100">
                    <h3 class="text-danger">Hazara Public School & College Jamber</h3>
                    <p class="capitalized"><strong>Class:</strong> <?= $row['class_name'] ?></p>
                    <p class="capitalized"><strong>Session:</strong> <?= $row['session_name'] ?></p>
                    <p class="capitalized"><strong>Section:</strong> <?= $row['section_name'] ?></p>
                    <p class="capitalized"><strong>Student Name:</strong> <?= $row['student_name'] ?></p>
                    <p class="capitalized"><strong>Father Name:</strong> <?= $row['father_name'] ?></p>
                    <p class="capitalized"><strong>Family Code:</strong> <?= $row['family_code'] ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class='text-danger'>No students found for the selected filters.</p>
        <?php endif; ?>
    </div>
    <div class="print-btn">
        <button onclick="window.print()" class="btn btn-success">Print ID Cards</button>
    </div>
</div>

<?php include 'footer.php'; // Include the footer 
?>