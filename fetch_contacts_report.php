<?php
include 'confiq.php'; // Include database configuration

// Initialize an empty response
$response = '';

// Get filter parameters
$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';
$session = $_GET['session'] ?? '';

// Build the SQL query with optional filters and the 'active' status condition
$query = "SELECT 
            student_name, 
            family_code,
            father_name, 
            mother_name, 
            father_cell_no, 
            mother_cell_no, 
            home_cell_no,
            whatsapp_number, 
            email 
          FROM students 
          WHERE status = 'active'"; // Ensure only active students are fetched

if (!empty($class)) {
    $query .= " AND class_id = '$class'";
}
if (!empty($section)) {
    $query .= " AND section_id = '$section'";
}
if (!empty($session)) {
    $query .= " AND session = '$session'";
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
                        <th>Mother Name</th>
                        <th>Father Cell No</th>
                        <th>Mother Cell No</th>
                        <th>Home Cell No</th>
                        <th>Email</th>
                    </tr>
                  </thead>';
    $response .= '<tbody>';
    while ($row = $result->fetch_assoc()) {
        $response .= '<tr>
                        <td>' . htmlspecialchars($row['student_name']) . '</td>
                        <td>' . htmlspecialchars($row['family_code']) . '</td>
                        <td>' . htmlspecialchars($row['father_name']) . '</td>
                        <td>' . htmlspecialchars($row['mother_name']) . '</td>
                        <td>' . htmlspecialchars($row['father_cell_no']) . '</td>
                        <td>' . htmlspecialchars($row['mother_cell_no']) . '</td>
                        <td>' . htmlspecialchars($row['home_cell_no']) . '</td>
                        <td>' . htmlspecialchars($row['email']) . '</td>
                      </tr>';
    }
    $response .= '</tbody></table>';
} else {
    $response = '<p class="text-center">No data available for the selected filters.</p>';
}

// Return the response
echo $response;
