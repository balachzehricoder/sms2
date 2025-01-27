<?php
include 'confiq.php'; // Include database configuration

$search = $_GET['search'] ?? '';

// Base query to fetch struck-off students
$query = "
    SELECT 
        s.student_id, 
        s.family_code, 
        s.student_name, 
        c.class_name, 
        sec.section_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN sections sec ON s.section_id = sec.section_id
    WHERE s.status = 'struck_off'";

// Add search condition
if (!empty($search)) {
    $query .= " AND s.student_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $sno = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . $sno++ . "</td>
            <td>" . htmlspecialchars($row['student_name']) . "</td>
            <td>" . htmlspecialchars($row['family_code']) . "</td>
            <td>" . htmlspecialchars($row['class_name']) . "</td>
            <td>" . htmlspecialchars($row['section_name']) . "</td>
            <td>
                <form method='POST' action='reinstate_student.php' style='display:inline;'>
                    <input type='hidden' name='student_id' value='" . $row['student_id'] . "'>
                    <button type='submit' class='btn btn-success'>Reinstate</button>
                </form>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No struck-off students found.</td></tr>";
}
