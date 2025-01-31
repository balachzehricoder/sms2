<?php
include 'confiq.php'; // Include database configuration

// Initialize an empty response
$response = '';

// Get filter parameters
$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';
$session = $_GET['session'] ?? '';

// Build the SQL query with filters and calculate age
$query = "SELECT 
            students.student_name, 
            students.dob,
            TIMESTAMPDIFF(YEAR, students.dob, CURDATE()) AS age_years,
            TIMESTAMPDIFF(MONTH, students.dob, CURDATE()) % 12 AS age_months,
            students.family_code,
            students.father_name,
            classes.class_name,
            sections.section_name,
            sessions.session_name
          FROM students
          LEFT JOIN classes ON students.class_id = classes.class_id
          LEFT JOIN sections ON students.section_id = sections.section_id
          LEFT JOIN sessions ON students.session = sessions.id
          WHERE students.status = 'active'";

if (!empty($class)) {
    $query .= " AND students.class_id = '$class'";
}
if (!empty($section)) {
    $query .= " AND students.section_id = '$section'";
}
if (!empty($session)) {
    $query .= " AND students.session = '$session'";
}

// Execute the query
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // Create the table rows with data
    $response .= '<table class="table table-striped table-bordered">';
    $response .= '<thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Family Code</th>
                        <th>Father Name</th>
                        <th>Class</th>
                        <th>Date of Birth</th>
                        <th>Age (Years)</th>
                        <th>Section</th>
                        <th>Session</th>
                    </tr>
                  </thead>';
    $response .= '<tbody>';
    while ($row = $result->fetch_assoc()) {
        $rounded_age = $row['age_months'] >= 6 ? $row['age_years'] + 1 : $row['age_years']; // Round age to nearest year
        $response .= '<tr>
                        <td>' . htmlspecialchars($row['student_name']) . '</td>
                        <td>' . htmlspecialchars($row['family_code']) . '</td>
                        <td>' . htmlspecialchars($row['father_name']) . '</td>
                        <td>' . htmlspecialchars($row['class_name']) . '</td>
                        <td>' . htmlspecialchars($row['dob']) . '</td>
                        <td>' . $rounded_age . '</td>
                        <td>' . htmlspecialchars($row['section_name']) . '</td>
                        <td>' . htmlspecialchars($row['session_name']) . '</td>
                      </tr>';
    }
    $response .= '</tbody></table>';
} else {
    $response = '<p class="text-center">No data available for the selected filters.</p>';
}

// Return the response
echo $response;
