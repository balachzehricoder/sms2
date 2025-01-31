<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

    <div class="form-container">
        <h3>Generate ID Cards</h3>
        <form action="id_cards.php" method="GET">
            <!-- Class Dropdown -->
            <div class="form-group">
                <label for="class">Class</label>
                <select name="class" id="class" required>
                    <option value="">Select Class</option>
                    <?php
                    $classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                    while ($class = $classes->fetch_assoc()) {
                        echo "<option value='{$class['class_id']}'>{$class['class_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Section Dropdown -->
            <div class="form-group">
                <label for="section">Section</label>
                <select name="section" id="section" required>
                    <option value="">Select Section</option>
                    <?php
                    $sections = $conn->query("SELECT * FROM sections ORDER BY section_name ASC");
                    while ($section = $sections->fetch_assoc()) {
                        echo "<option value='{$section['section_id']}'>{$section['section_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Session Dropdown -->
            <div class="form-group">
                <label for="session">Session</label>
                <select name="session" id="session" required>
                    <option value="">Select Session</option>
                    <?php
                    $sessions = $conn->query("SELECT * FROM sessions ORDER BY session_name ASC");
                    while ($session = $sessions->fetch_assoc()) {
                        echo "<option value='{$session['id']}'>{$session['session_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit">Generate ID Cards</button>
            </div>
        </form>
    </div>
<? include "footer.php";
?>