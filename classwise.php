<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all classes with student count (even if no students exist in a class)
$studentQuery = "SELECT
                    classes.class_id,
                    classes.class_name,
                    COUNT(students.student_id) AS total_students,
                    SUM(CASE WHEN students.gender = 'Male' THEN 1 ELSE 0 END) AS male_count,
                    SUM(CASE WHEN students.gender = 'Female' THEN 1 ELSE 0 END) AS female_count
                 FROM classes
                 LEFT JOIN students ON classes.class_id = students.class_id
                 GROUP BY classes.class_id, classes.class_name
                 ORDER BY CAST(classes.class_name AS UNSIGNED) ASC";

$studentResult = $conn->query($studentQuery);
?>

<div class="content-body">
	<div class="row page-titles mx-0">
		<div class="col p-md-0">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
				<li class="breadcrumb-item active"><a href="javascript:void(0)">Class-wise Gender Report</a></li>
			</ol>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Class-wise Gender Report</h4>
						<div class="table-responsive">
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.No</th>
										<th>Class Name</th>
										<th>Total Students</th>
										<th>Boys</th>
										<th>Girls</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ($studentResult->num_rows > 0) {
										$sno = 1;
										while ($studentRow = $studentResult->fetch_assoc()) {
											echo "<tr>
                                                    <td>" . $sno++ . "</td>
                                                    <td>" . htmlspecialchars($studentRow['class_name']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['total_students'] ?? 0) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['male_count'] ?? 0) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['female_count'] ?? 0) . "</td>
                                                    <td>
                                                        <a href='view_class.php?class_id=" . $studentRow['class_id'] . "' class='btn btn-primary'>View Class</a>
                                                        <a href='update_students.php?class_id=" . $studentRow['class_id'] . "' class='btn btn-success'>Update Student Info</a>
                                                        <a href='update_pictures.php?class_id=" . $studentRow['class_id'] . "' class='btn btn-warning'>Update Pictures</a>
                                                        <a href='update_fee.php?class_id=" . $studentRow['class_id'] . "' class='btn btn-info'>Update Fee</a>
                                                        <a href='print_class.php?class_id=" . $studentRow['class_id'] . "' class='btn btn-secondary'>Print</a>
                                                        <a href='export_class.php?class_id=" . $studentRow['class_id'] . "' class='btn btn-dark'>Export</a>
                                                    </td>
                                                  </tr>";
										}
									} else {
										echo "<tr><td colspan='6'>No data found</td></tr>";
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include 'footer.php'; ?>
