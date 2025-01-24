<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if class_id is passed via URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id === 0) {
    echo "<div class='alert alert-danger'>Invalid Class ID.</div>";
    exit;
}

// Fetch all students in the selected class with their images
$studentQuery = "SELECT student_id, student_name, student_image FROM students WHERE class_id = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param('i', $class_id);
$stmt->execute();
$studentResult = $stmt->get_result();
?>

<div class="content-body">
	<div class="row page-titles mx-0">
		<div class="col p-md-0">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
				<li class="breadcrumb-item active"><a href="javascript:void(0)">Update Student Pictures</a></li>
			</ol>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Update Student Pictures</h4>
						<div class="table-responsive">
							<form action="update_pictures_action.php" method="POST" enctype="multipart/form-data">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>S.No</th>
											<th>Student Name</th>
											<th>Current Image</th>
											<th>Update Image</th>
										</tr>
									</thead>
									<tbody>
										<?php
										if ($studentResult->num_rows > 0) {
											$sno = 1;
											while ($studentRow = $studentResult->fetch_assoc()) {
												$student_id = $studentRow['student_id'];
												$current_image = $studentRow['student_image'] ? $studentRow['student_image'] : 'default.jpg';
												echo "<tr>
													<td>" . $sno++ . "</td>
													<td>" . htmlspecialchars($studentRow['student_name']) . "</td>
													<td><img src='" . htmlspecialchars($current_image) . "' alt='Current Image' width='100'></td>
													<td><input type='file' name='image_$student_id' accept='image/*'></td>
													<input type='hidden' name='student_ids[]' value='" . $student_id . "'>
												</tr>";
											}
										} else {
											echo "<tr><td colspan='4'>No students found.</td></tr>";
										}
										?>
									</tbody>
								</table>
								<button type="submit" class="btn btn-primary">Update Images</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include 'footer.php'; ?>
