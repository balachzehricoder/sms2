<?php
include 'confiq.php'; // Include database configuration
include 'header.php'; // Include the header
include 'sidebar.php'; // Include the sidebar

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all classes and sessions for dropdowns
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC") or die($conn->error);
$sessions = $conn->query("SELECT * FROM sessions ORDER BY session_name ASC") or die($conn->error);
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Generate ID Cards</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Generate ID Cards</h4>

                        <!-- Filters Form -->
                        <form action="view_id_cards.php" method="GET" id="filterForm">
                            <div class="row">
                                <!-- Class Dropdown -->
                                <div class="col-md-4">
                                    <label for="class" class="form-label">Class</label>
                                    <select name="class" id="class" class="form-control" required>
                                        <option value="">Select Class</option>
                                        <?php while ($class = $classes->fetch_assoc()) { ?>
                                            <option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Session Dropdown -->
                                <div class="col-md-4">
                                    <label for="session" class="form-label">Session</label>
                                    <select name="session" id="session" class="form-control" required>
                                        <option value="">Select Session</option>
                                        <?php while ($session = $sessions->fetch_assoc()) { ?>
                                            <option value="<?= $session['id'] ?>"><?= $session['session_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Include the footer 
?>