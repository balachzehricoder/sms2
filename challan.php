<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Fetch classes, sections, and sessions
$classes_query = "SELECT class_id, class_name FROM classes ORDER BY class_name ASC";
$classes_result = $conn->query($classes_query);

$sections_query = "SELECT section_id, section_name FROM sections ORDER BY section_name ASC";
$sections_result = $conn->query($sections_query);

// Predefined sessions and months
$sessions = ["2025-2026", "2027-2028", "2029-2030"];
$months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];
$fee_types = ["Monthly", "Quarterly", "Yearly"];
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Fee Challans</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="addchallan.php" method="post">

                            <h4 class="card-title">Create Fee Challans (0 records)</h4>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="session">Session</label>
                                    <select class="form-control" id="session" name="session">
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?= htmlspecialchars($session) ?>"><?= htmlspecialchars($session) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="class-id">Class</label>
                                    <select class="form-control" id="class-id" name="class_id">
                                        <option value="" disabled selected>Select Class</option>
                                        <?php
                                        if ($classes_result->num_rows > 0) {
                                            while ($row = $classes_result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row['class_id']) . "'>" . htmlspecialchars($row['class_name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>No Classes Found</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="section-id">Section</label>
                                    <select class="form-control" id="section-id" name="section_id">
                                        <option value="" disabled selected>All</option>
                                        <?php
                                        if ($sections_result->num_rows > 0) {
                                            while ($row = $sections_result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row['section_id']) . "'>" . htmlspecialchars($row['section_name']) . "</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>No Sections Found</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="challan-month">Challan Month</label>
                                    <select class="form-control" id="challan-month" name="challan_month">
                                        <?php foreach ($months as $month): ?>
                                            <option value="<?= htmlspecialchars($month) ?>"><?= htmlspecialchars($month) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="due-date">Due Date</label>
                                    <input type="date" class="form-control" id="due-date" name="due_date" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="fee-type">Fee Type</label>
                                    <select class="form-control" id="fee-type" name="fee_type">
                                        <?php foreach ($fee_types as $fee_type): ?>
                                            <option value="<?= htmlspecialchars($fee_type) ?>"><?= htmlspecialchars($fee_type) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-success btn-block">Create Challan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';
?>
