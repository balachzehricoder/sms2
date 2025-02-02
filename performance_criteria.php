<?php
// performance_criteria.php
include 'confiq.php';    // Database connection (MySQLi)
include 'header.php';    // Contains layout header, <body>, etc.
include 'sidebar.php';   // Sidebar navigation
error_reporting(E_ALL);

// Handle form submission to save remarks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_criteria'])) {
    // First, clear existing remarks (if you want to reset them)
    $conn->query("DELETE FROM criteria_remarks");

    // Loop through the posted remarks and scores
    for ($i = 1; $i <= 5; $i++) {
        $remark = trim($_POST["remark_$i"]);
        $score = intval($_POST["score_$i"]);

        // Insert into the database
        if (!empty($remark) && $score > 0) {
            $stmt = $conn->prepare("INSERT INTO criteria_remarks (score, remark) VALUES (?, ?)");
            $stmt->bind_param("is", $score, $remark);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Display success message
    $message = "Criteria saved successfully!";
}
?>

<!-- MAIN CONTENT -->
<div class="content-body">
    <div class="container-fluid">
        <h2>Academic Performance Criteria</h2>

        <!-- Success Message -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Criteria Form -->
        <form method="POST" action="">
            <input type="hidden" name="save_criteria" value="1">

            <div class="row">
                <div class="col-md-6">
                    <label><strong>Remarks</strong></label>
                </div>
                <div class="col-md-6">
                    <label><strong>Marks</strong></label>
                </div>
            </div>

            <!-- Remark and Score Inputs -->
            <?php
            $defaultRemarks = ['Excellent', 'V.Good', 'Not Good', 'Poor', 'Very Poor'];
            $defaultScores = [5, 4, 3, 2, 1];
            for ($i = 1; $i <= 5; $i++):
            ?>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="remark_<?php echo $i; ?>"
                            value="<?php echo htmlspecialchars($defaultRemarks[$i - 1]); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="score_<?php echo $i; ?>"
                            value="<?php echo $defaultScores[$i - 1]; ?>" min="1" max="5" required>
                    </div>
                </div>
            <?php endfor; ?>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary mt-3">Save Criteria</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>