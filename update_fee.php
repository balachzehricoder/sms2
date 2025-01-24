<?php
include 'confiq.php';

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id === 0) {
    echo "<script>alert('Invalid Class ID'); window.location.href='classes.php';</script>";
    exit;
}

// Fetch current fee for the class
$query = "SELECT class_name, standard_monthly_fee FROM classes WHERE class_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classData = $result->fetch_assoc();

if (!$classData) {
    echo "<script>alert('Class not found'); window.location.href='classes.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Fee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: 1px solid #ddd;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            font-size: 1.5rem;
            color: #343a40;
        }
        .form-control {
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-primary:hover, .btn-secondary:hover {
            opacity: 0.9;
        }
        .text-center h4 {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center">Update Fee for Class <?php echo htmlspecialchars($classData['class_name']); ?></h4>
                <form action="update_fee_action.php" method="POST">
                    <div class="form-group">
                        <label for="standard_monthly_fee">Standard Monthly Fee</label>
                        <input type="number" name="standard_monthly_fee" id="standard_monthly_fee" 
                               value="<?php echo htmlspecialchars($classData['standard_monthly_fee']); ?>" 
                               class="form-control" min="0" step="1" required>
                    </div>
                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">Update Fee</button>
                        <a href="classes.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
