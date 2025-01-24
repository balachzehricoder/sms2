<?php
include 'confiq.php';

if (isset($_GET['family_code'])) {
    $family_code = $_GET['family_code'];

    $query = "SELECT religion, whatsapp_number, father_cell_no, mother_cell_no, home_cell_no, state, city, father_name, mother_name, home_address FROM students WHERE family_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $family_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }

    $stmt->close();
}
$conn->close();
?>
