<?php
// Include necessary configuration file
include 'confiq.php';

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the filter data is provided via GET request
$class_id = isset($_GET['class']) ? $_GET['class'] : '';
$section_id = isset($_GET['section']) ? $_GET['section'] : '';
$session_id = isset($_GET['session']) ? $_GET['session'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';

// Build the SQL query with dynamic WHERE conditions based on filters
$query = "SELECT students.student_id, students.student_name, classes.class_name, sections.section_name, students.gender, students.dob, students.father_name
          FROM students
          LEFT JOIN classes ON students.class_id = classes.class_id
          LEFT JOIN sections ON students.section_id = sections.section_id
          LEFT JOIN sessions ON students.session = sessions.id
          WHERE 1";

// Apply filters to the query if they are provided
if ($class_id) {
    $query .= " AND students.class_id = '$class_id'";
}
if ($section_id) {
    $query .= " AND students.section_id = '$section_id'";
}
if ($session_id) {
    $query .= " AND students.session = '$session_id'";
}
if ($gender) {
    $query .= " AND students.gender = '$gender'";
}

// Execute the query
$result = $conn->query($query);

// Check if there are any records to display
if ($result->num_rows > 0) {
    // Output the results in table rows
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['student_id'] . '</td>';
        echo '<td>' . $row['student_name'] . '</td>';
        echo '<td>' . $row['class_name'] . '</td>';
        echo '<td>' . $row['section_name'] . '</td>';
        echo '<td>' . ucfirst($row['gender']) . '</td>';
        echo '<td>' . $row['dob'] . '</td>';
        echo '<td>' . $row['father_name'] . '</td>';
        echo '<td>
                <a href="edit_student.php?id=' . $row['student_id'] . '" class="btn btn-sm btn-primary">Edit</a>
                <a href="delete_student.php?id=' . $row['student_id'] . '" class="btn btn-sm btn-danger">Delete</a>
              </td>';
        echo '</tr>';
    }
} else {
    // If no records match the filters, display a message
    echo '<tr><td colspan="8" class="text-center">No data available for the selected filters.</td></tr>';
}

// Close the database connection
$conn->close();
?>
