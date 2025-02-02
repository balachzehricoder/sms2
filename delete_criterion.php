<?php
// delete_criterion.php
include 'confiq.php';
error_reporting(E_ALL);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('No criterion ID provided.');window.location.href='performance_heads.php';</script>";
    exit;
}

$criterion_id = intval($_GET['id']);

// Prepare the delete statement
$sql = "DELETE FROM performance_criteria WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $criterion_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo "<script>alert('Criterion deleted successfully.');window.location.href='performance_heads.php';</script>";
    exit;
} else {
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    echo "<script>alert('Error deleting criterion: $error');window.location.href='performance_heads.php';</script>";
    exit;
}
