<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Sessions
$sessions = $conn->query("SELECT DISTINCT TRIM(challan_session) AS session_name FROM challans ORDER BY challan_session DESC");

// Fetch Classes
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC");

// Fetch Sections
$sections = $conn->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC");

// Define months
$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// Get Filters
$selected_session = isset($_GET['session']) ? trim($_GET['session']) : '';
$selected_month = isset($_GET['month']) ? trim($_GET['month']) : '';
$selected_class = isset($_GET['class_id']) ? trim($_GET['class_id']) : '';
$selected_section = isset($_GET['section_id']) ? trim($_GET['section_id']) : '';

// Build the WHERE clause
$where = "c.discount > 0"; // Only fetch students who got a discount
if (!empty($selected_session)) $where .= " AND TRIM(challan_session) = TRIM('$selected_session')";
if (!empty($selected_month)) $where .= " AND TRIM(challan_month) = TRIM('$selected_month')";
if (!empty($selected_class)) $where .= " AND s.class_id = '$selected_class'";
if (!empty($selected_section)) $where .= " AND s.section_id = '$selected_section'";

// Fetch Discount Data
$query = "
    SELECT 
        c.challan_id, 
        s.student_id, 
        s.student_name, 
        s.father_name, 
        cl.class_name, 
        sec.section_name, 
        c.discount
    FROM challans c
    JOIN students s ON c.student_id = s.student_id
    JOIN classes cl ON s.class_id = cl.class_id
    JOIN sections sec ON s.section_id = sec.section_id
    WHERE $where
    ORDER BY cl.class_name, s.student_name ASC";

$result = $conn->query($query);
?>

<div class="content-body">
    <div class="container-fluid">
        <h4 class="card-title">Monthly Discount Report</h4>

        <!-- Filters -->
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-3">
                    <label>Session</label>
                    <select name="session" class="form-control" required>
                        <option value="">Select Session</option>
                        <?php while ($session = $sessions->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($session['session_name']); ?>" <?= ($selected_session == $session['session_name']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($session['session_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Class</label>
                    <select name="class_id" class="form-control">
                        <option value="">All</option>
                        <?php while ($class = $classes->fetch_assoc()): ?>
                            <option value="<?= $class['class_id']; ?>" <?= ($selected_class == $class['class_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Section</label>
                    <select name="section_id" class="form-control">
                        <option value="">All</option>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <option value="<?= $section['section_id']; ?>" <?= ($selected_section == $section['section_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($section['section_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Month</label>
                    <select name="month" class="form-control" required>
                        <option value="">Select Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= $month; ?>" <?= ($selected_month == $month) ? 'selected' : ''; ?>>
                                <?= $month; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <!-- Export & Print Buttons -->
        <?php if (!empty($selected_session) && !empty($selected_month)): ?>
            <div class="mt-3 no-print">
                <a href="export_discount_report.php?session=<?= $selected_session ?>&month=<?= $selected_month ?>&class_id=<?= $selected_class ?>&section_id=<?= $selected_section ?>" class="btn btn-success">Export to Excel</a>
                <button onclick="printReport()" class="btn btn-secondary">Print Report</button>
            </div>
        <?php endif; ?>

        <!-- Printable Section -->
        <div id="printable-section">
            <!-- Table -->
            <div class="mt-4">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Student Name</th>
                            <th>Father Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Challan Discount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $serial = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $serial++; ?></td>
                                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                                    <td><?= htmlspecialchars($row['father_name']); ?></td>
                                    <td><?= htmlspecialchars($row['class_name']); ?></td>
                                    <td><?= htmlspecialchars($row['section_name']); ?></td>
                                    <td><?= number_format($row['discount'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No data available for the selected criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printable-section,
        #printable-section * {
            visibility: visible;
        }

        #printable-section {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        .sidebar,
        .header,
        .footer,
        .btn,
        .no-print {
            display: none !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        h4 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 10px;
        }
    }
</style>

<!-- Print Script -->
<script>
    function printReport() {
        window.print();
    }
</script>

<?php require_once 'footer.php'; ?>