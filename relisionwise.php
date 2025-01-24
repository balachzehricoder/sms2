<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all classes, sessions, and religions for dropdowns
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC") or die($conn->error);
$sessions = $conn->query("SELECT * FROM sessions ORDER BY session_name ASC") or die($conn->error);
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name ASC") or die($conn->error);
$religions = $conn->query("SELECT DISTINCT religion FROM students ORDER BY religion ASC") or die($conn->error);
?>

<div class="content-body">
	<div class="row page-titles mx-0">
		<div class="col p-md-0">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
				<li class="breadcrumb-item active"><a href="javascript:void(0)">Religion-wise Report</a></li>
			</ol>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Religion-wise Report</h4>

						<!-- Filters Form -->
						<form id="filterForm">
							<div class="row">
								<!-- Class Dropdown -->
								<div class="col-md-3">
									<label for="class" class="form-label">Class</label>
									<select name="class" id="class" class="form-control">
										<option value="">Select Class</option>
										<?php
										while ($class = $classes->fetch_assoc()) { ?>
										<option value="<?= $class['class_id'] ?>"><?= $class['class_name'] ?></option>
										<?php
									} ?>
									</select>
								</div>

								<!-- Section Dropdown -->
								<div class="col-md-3">
									<label for="section" class="form-label">Section</label>
									<select name="section" id="section" class="form-control">
										<option value="">Select Section</option>
										<?php
										while ($section = $sections->fetch_assoc()) { ?>
										<option value="<?= $section['section_id'] ?>"><?= $section['section_name'] ?></option>
										<?php
									} ?>
									</select>
								</div>

								<!-- Session Dropdown -->
								<div class="col-md-3">
									<label for="session" class="form-label">Session</label>
									<select name="session" id="session" class="form-control">
										<option value="">Select Session</option>
										<?php
										while ($session = $sessions->fetch_assoc()) { ?>
										<option value="<?= $session['id'] ?>"><?= $session['session_name'] ?></option>
										<?php
									} ?>
									</select>
								</div>

								<!-- Religion Dropdown -->
								<div class="col-md-3">
									<label for="religion" class="form-label">Religion</label>
									<select name="religion" id="religion" class="form-control">
										<option value="">Select Religion</option>
										<?php
										while ($religion = $religions->fetch_assoc()) { ?>
										<option value="<?= $religion['religion'] ?>"><?= $religion['religion'] ?></option>
										<?php
									} ?>
									</select>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-12">
									<button type="button" id="searchBtn" class="btn btn-primary">Search</button>
								</div>
							</div>
						</form>

						<!-- Results Table -->
						<div class="mt-4">
							<h5>Results</h5>
							<div class="table-responsive" id="resultsTable">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Student ID</th>
											<th>Student Name</th>
											<th>Class</th>
											<th>Section</th>
											<th>Religion</th>
											<th>Date of Birth</th>
											<th>Gender</th>
											<th>Father Name</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="9" class="text-center">No data available. Use the filters to fetch results.</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Include JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"> </script>
<script>
	$(document).ready(function () {
		// Update Section Dropdown Based on Class
		$('#class').change(function () {
			var classId = $(this).val();
			$.ajax({
				url: 'https://jamber.hazaraacademy.com/fetch_sections',  // Full URL or relative path
				type: 'POST',
				data: { class_id: classId },
				success: function (response) {
					$('#section').html(response);
				},
				error: function () {
					alert('Failed to fetch sections. Please try again.');
				}
			});
		});


		// Fetch and Display Results
		$('#searchBtn').click(function () {
			var formData = $('#filterForm').serialize();
			$.ajax({
				url: 'fetch_religion_report',
				type: 'GET',
				data: formData,
				success: function (response) {
					$('#resultsTable').html(response);
				},
				error: function () {
					alert('Failed to fetch results. Please try again.');
				}
			});
		});
	});
</script>


<?php include 'footer.php'; ?>
