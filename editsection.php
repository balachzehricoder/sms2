<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';
if (isset($_GET['section_id'])) {
    $section_id = $_GET['section_id'];

    // Fetch section details
    $sectionQuery = "SELECT section_id, section_name, class_id FROM sections WHERE section_id = ?";
    $stmt = $conn->prepare($sectionQuery);
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $section = $result->fetch_assoc();

    // Fetch classes for the dropdown
    $classQuery = "SELECT class_id, class_name FROM classes ORDER BY class_name ASC";
    $classResult = $conn->query($classQuery);
}



include 'confiq.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $section_id = $_POST['section_id'];
    $section_name = $_POST['section_name'];
    $class_id = $_POST['class_id'];

    $updateQuery = "UPDATE sections SET section_name = ?, class_id = ? WHERE section_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sii", $section_name, $class_id, $section_id);

    if ($stmt->execute()) {
        header("Location: section?update_success=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<div class="container d-flex justify-content-center align-items-center min-vh-100">

<div class="modal-content w-50">
    <div class="modal-header">
        <h5 class="modal-title">Edit Section</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="basic-form">
            <form action="" method="post">
                <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section['section_id']); ?>">

                <!-- Section Name Input -->
                <div class="form-group">
                    <label for="section-name">Section Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="section-name" name="section_name" 
                           value="<?php echo htmlspecialchars($section['section_name']); ?>" required>
                </div>

                <!-- Class Name Dropdown -->
                <div class="form-group">
                    <label for="class-id">Class Name <span class="text-danger">*</span></label>
                    <select class="form-control" id="class-id" name="class_id" required>
                        <?php
                        while ($classRow = $classResult->fetch_assoc()) {
                            $selected = $classRow['class_id'] == $section['class_id'] ? 'selected' : '';
                            echo "<option value='" . $classRow['class_id'] . "' $selected>" . htmlspecialchars($classRow['class_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<?php
$conn->close();
include 'footer.php';
?>
