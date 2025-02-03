<?php
include 'confiq.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the file paths to delete the images
    $query = "SELECT exam_head_signature, principal_signature FROM signatures WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $examHeadPath, $principalPath);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Delete files from the server
    if (file_exists($examHeadPath)) unlink($examHeadPath);
    if (file_exists($principalPath)) unlink($principalPath);

    // Delete record from database
    $deleteQuery = "DELETE FROM signatures WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: signature_settings.php");
    exit();
}
