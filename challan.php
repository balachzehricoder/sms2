<?php
include 'confiq.php';  // Database connection
include 'header.php';  // Optional
include 'sidebar.php'; // Optional

// Fetch classes
$classes = [];
$resClasses = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");
while ($row = $resClasses->fetch_assoc()) {
    $classes[] = $row;
}

// Fetch sections (optional)
$sections = [];
$resSections = $conn->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC");
while ($row = $resSections->fetch_assoc()) {
    $sections[] = $row;
}

// Fetch all students (in case you want a "single student" option)
$allStudents = [];
$resAllStu = $conn->query("SELECT student_id, student_name FROM students ORDER BY student_name ASC");
while ($row = $resAllStu->fetch_assoc()) {
    $allStudents[] = $row;
}

// Predefined sessions (example) and months
$sessions = ["2025-2026", "2027-2028", "2029-2030"];
$months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
];
?>

<div class="content-body">
    <div class="container-fluid">
        <h2>Create Fee Challans</h2>

        <form action="addchallan.php" method="post">
            <div class="form-row">

                <!-- Session -->
                <div class="form-group col-md-3">
                    <label for="session">Session</label>
                    <select class="form-control" id="session" name="session" required>
                        <option value="" disabled selected>Select Session</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= htmlspecialchars($session) ?>">
                                <?= htmlspecialchars($session) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Challan Month -->
                <div class="form-group col-md-3">
                    <label for="challan-month">Challan Month</label>
                    <select class="form-control" id="challan-month" name="challan_month" required>
                        <option value="" disabled selected>Select Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= htmlspecialchars($month) ?>">
                                <?= htmlspecialchars($month) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Class -->
                <div class="form-group col-md-3">
                    <label for="class-id">Class</label>
                    <select class="form-control" id="class-id" name="class_id" required>
                        <option value="" disabled selected>Select Class</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?= $cls['class_id'] ?>">
                                <?= htmlspecialchars($cls['class_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section (optional) -->
                <div class="form-group col-md-3">
                    <label for="section-id">Section</label>
                    <select class="form-control" id="section-id" name="section_id">
                        <option value="" selected>All Sections</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['section_id'] ?>">
                                <?= htmlspecialchars($sec['section_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <!-- Single Student (optional) -->
                <div class="form-group col-md-3">
                    <label for="single-student">Single Student (Optional)</label>
                    <select class="form-control" id="single-student" name="single_student_id">
                        <option value="">All Students in Class/Section</option>
                        <?php foreach ($allStudents as $stu): ?>
                            <option value="<?= $stu['student_id'] ?>">
                                <?= htmlspecialchars($stu['student_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Due Date -->
                <div class="form-group col-md-3">
                    <label for="due-date">Due Date</label>
                    <input type="date" class="form-control" id="due-date" name="due_date" required>
                </div>

                <!-- Include Discounts? -->
                <div class="form-group col-md-3">
                    <label for="include-discounts">Include Discounts</label>
                    <select class="form-control" id="include-discounts" name="include_discounts" required>
                        <option value="yes" selected>Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <!-- Custom Fee Override -->
                <div class="form-group col-md-3">
                    <label for="custom-fee">Custom Default Monthly Fee</label>
                    <input
                        type="number"
                        step="0.01"
                        class="form-control"
                        id="custom-fee"
                        name="default_fee"
                        placeholder="e.g. 2000. Leave blank to use student's fee">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Create Challans</button>
        </form>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';
?>