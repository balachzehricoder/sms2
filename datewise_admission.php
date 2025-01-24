<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all admissions grouped by date
$admissionQuery = "SELECT
                    DATE(students.date_of_admission) AS admission_date,
                    COUNT(students.student_id) AS total_students,
                    SUM(CASE WHEN students.gender = 'Male' THEN 1 ELSE 0 END) AS male_count,
                    SUM(CASE WHEN students.gender = 'Female' THEN 1 ELSE 0 END) AS female_count
                 FROM students
                 GROUP BY DATE(students.date_of_admission)
                 ORDER BY DATE(students.date_of_admission) DESC";

$admissionResult = $conn->query($admissionQuery);
?>

<div class="content-body">
	<div class="row page-titles mx-0">
		<div class="col p-md-0">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
				<li class="breadcrumb-item active"><a href="javascript:void(0)">Date-wise Admissions Report</a></li>
			</ol>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Date-wise Admissions Report</h4>
						<div class="table-responsive">
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.No</th>
										<th>Admission Date</th>
										<th>Total Students</th>
										<th>Boys</th>
										<th>Girls</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ($admissionResult->num_rows > 0) {
										$sno = 1;
										while ($admissionRow = $admissionResult->fetch_assoc()) {
											echo "<tr>
                                                    <td>" . $sno++ . "</td>
                                                    <td>" . htmlspecialchars($admissionRow['admission_date']) . "</td>
                                                    <td>" . htmlspecialchars($admissionRow['total_students'] ?? 0) . "</td>
                                                    <td>" . htmlspecialchars($admissionRow['male_count'] ?? 0) . "</td>
                                                    <td>" . htmlspecialchars($admissionRow['female_count'] ?? 0) . "</td>
                                                    <td>
                                                        <a href='view_admissions.php?admission_date=" . $admissionRow['admission_date'] . "' class='btn btn-primary'>View Admissions</a>
                                                        <a href='print_admissions.php?admission_date=" . $admissionRow['admission_date'] . "' class='btn btn-secondary'>Print</a>
                                                        <a href='export_admissions.php?admission_date=" . $admissionRow['admission_date'] . "' class='btn btn-dark'>Export</a>
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
