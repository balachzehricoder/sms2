<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';
error_reporting(E_ALL);
echo "hellow";
// Fetch students from the database
$studentQuery = "SELECT student_id, family_code, student_name, session, class_id, section_id, gr_no, gender, religion, dob, date_of_admission, whatsapp_number, father_cell_no, mother_cell_no, home_cell_no, place_of_birth, state, city, email, father_name, mother_name, home_address, created_at, student_image FROM students ORDER BY student_name ASC";
$studentResult = $conn->query($studentQuery);
?>

<?php
if (isset($_GET['message'])) {
    echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
}
?>


<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Students</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">All Students</h4>
                            <div>
								<button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-student">
									<i class="fa fa-plus-circle"></i> Add New Student
								</button>
								<a href="student_export.php">
									<button type="button" class="btn btn-rounded btn-warning">
										Export to Excel
									</button>
								</a>
							</div>
							
                        </div>

                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Family Code</th>
                                        <th>Student Name</th>
                                        <th>Session</th>
                                        <th>Class ID</th>
                                        <th>Section ID</th>
                                        <th>Gender</th>
                                        <th>Religion</th>
                                        <th>Date of Birth</th>
                                        <th>Date of Admission</th>
                                        <th>Father's Cell No</th>
                                        <th>Mother's Cell No</th>
                                        <th>State</th>
                                        <th>City</th>
                                        <th>Email</th>
                                        <th>Father's Name</th>
                                        <th>Mother's Name</th>
                                        <th>Home Address</th>
                                        <th>Created At</th>
                                        <th>Student Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($studentResult->num_rows > 0) {
                                        while ($studentRow = $studentResult->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>" . htmlspecialchars($studentRow['student_id']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['family_code']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['student_name']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['session']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['class_id']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['section_id']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['gender']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['religion']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['dob']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['date_of_admission']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['father_cell_no']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['mother_cell_no']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['state']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['city']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['email']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['father_name']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['mother_name']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['home_address']) . "</td>
                                                    <td>" . htmlspecialchars($studentRow['created_at']) . "</td>
                                                    <td><img src='" . htmlspecialchars($studentRow['student_image']) . "' alt='Student Image' width='50'></td>
                                                    <td>
                                                        
                                                        <a href='delete_student?student_id=" . urlencode($studentRow['student_id']) . "' class='btn btn-danger'>Delete</a>
                                                    </td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='24'>No students found</td></tr>";
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
<div class="modal fade" id="add-new-student">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add New Student</h5>
				<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="basic-form">
					<form action="addstudent.php" method="post" enctype="multipart/form-data">

						<div class="form-group">
							<label for="family-code">Family Code</label>
							<input type="text" class="form-control" id="family-code" name="family_code" onkeyup="fetchFamilyDetails()">
						</div>

						<div class="form-group">
							<label for="student-name">Student Name</label>
							<input type="text" class="form-control" id="student-name" name="student_name" required>
						</div>

						<?php
						// Fetch sessions from the database
						$sessions = $conn->query("SELECT id, session_name FROM sessions");
						?>

						<div class="form-group">
							<label for="session">Session</label>
							<select class="form-control" id="session" name="session" required>
								<?php
								while ($row = $sessions->fetch_assoc()) : ?>
								<option value="<?php echo $row['id']; ?>"><?php echo $row['session_name']; ?></option>
								<?php
							endwhile; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="class-name">Class Name</label>
							<select class="form-control" id="class-name" name="class_name" required>
								<option value="">Select Class</option>
								<?php
								// Replace with your database connection
								include 'confiq.php';

								// Fetch classes
								$classQuery = "SELECT class_id, class_name FROM classes";
								$classResult = $conn->query($classQuery);

								if ($classResult->num_rows > 0) {
									while ($row = $classResult->fetch_assoc()) {
										echo '<option value="' . $row['class_id'] . '">' . $row['class_name'] . '</option>';
									}
								}
								?>
							</select>
						</div>

						<div class="form-group">
							<label for="section-name">Section Name</label>
							<select class="form-control" id="section-name" name="section_name" required>
								<option value="">Select Section</option>
								<?php
								// Include the database configuration file
								include 'confiq.php';

								// Query to fetch sections with student count
								$sectionQuery = "
                                    SELECT s.section_id, s.section_name,
                                           (SELECT COUNT(*) FROM students st WHERE st.section_id = s.section_id) AS student_count
                                    FROM sections s";

								// Execute the query
								$sectionResult = $conn->query($sectionQuery);

								if ($sectionResult->num_rows > 0) {
									while ($row = $sectionResult->fetch_assoc()) {
										echo '<option value="' . htmlspecialchars($row['section_id']) . '">'
										. htmlspecialchars($row['section_name'])
										. ' (' . htmlspecialchars($row['student_count']) . ' students)</option>';
									}
								} else {
									echo '<option value="">No sections available</option>';
								}
								?>
							</select>
						</div>

						<div class="form-group">
							<label for="gender">Gender</label>
							<select class="form-control" id="gender" name="gender" required>
								<option value="male">Male</option>
								<option value="female">Female</option>
							</select>
						</div>

						<div class="form-group">
							<label for="religion">Religion</label>
							<select class="form-control" id="religion" name="religion" required>
								<option value="">Select Religion</option>
								<option value="Islam">Islam</option>
								<option value="Christian">Christian</option>
								<option value="Others">Others</option>
							</select>
						</div>

						<div class="form-group">
							<label for="dob">Date of Birth</label>
							<input type="date" class="form-control" id="dob" name="dob" required>
						</div>

						<div class="form-group">
							<label for="date-of-admission">Date of Admission</label>
							<input type="date" class="form-control" id="date-of-admission" name="date_of_admission" required>
						</div>

						<div class="form-group">
							<label for="father-cell-no">Father's Cell No</label>
							<input type="text" class="form-control" id="father-cell-no" name="father_cell_no" required>
						</div>

						<div class="form-group">
							<label for="mother-cell-no">Mother's Cell No</label>
							<input type="text" class="form-control" id="mother-cell-no" name="mother_cell_no" required>
						</div>

						<div class="form-group">
							<label for="admission-fee">Admission Fee</label>
							<input type="number" class="form-control" id="admission-fee" name="admission_fee" placeholder="Enter Admission Fee" required>
						</div>

						<div class="form-group">
							<label for="monthly-fee">Monthly Fee</label>
							<input type="number" class="form-control" id="monthly-fee" name="monthly_fee" placeholder="Enter Monthly Fee" required>
						</div>

						<div class="form-group">
							<label for="state">State</label>
							<input type="text" class="form-control" id="state" name="state" required>
						</div>

						<div class="form-group">
							<label for="city">City</label>
							<input type="text" class="form-control" id="city" name="city" required>
						</div>

						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" class="form-control" id="email" name="email" required>
						</div>

						<div class="form-group">
							<label for="father-name">Father's Name</label>
							<input type="text" class="form-control" id="father-name" name="father_name" required>
						</div>

						<div class="form-group">
							<label for="mother-name">Mother's Name</label>
							<input type="text" class="form-control" id="mother-name" name="mother_name" required>
						</div>

						<div class="form-group">
							<label for="home-address">Home Address</label>
							<textarea class="form-control" id="home-address" name="home_address" required></textarea>
						</div>

						<div class="form-group">
							<label for="student-image">Student Image</label>
							<input type="file" class="form-control" id="student-image" name="student_image">
						</div>

						<div class="form-group">
							<button type="submit" class="btn btn-primary">Add Student</button>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	// Function to fetch family details based on the family code
	function fetchFamilyDetails()
	{
		var familyCode = document.getElementById("family-code").value.trim();  // Ensure no extra spaces

		// Check if family code is entered
		if (familyCode) {
			// Send AJAX request to fetch family details from the backend
			fetch('get_family_details.php?family_code=' + familyCode)
			.then(response => response.json())
			.then(data => {
				if (data) {
					// If data is fetched, populate the form fields
					setSelectValue('religion', data.religion || '');
					document.getElementById('father-cell-no').value = data.father_cell_no || '';
					document.getElementById('mother-cell-no').value = data.mother_cell_no || '';
					document.getElementById('state').value = data.state || '';
					document.getElementById('city').value = data.city || '';
					document.getElementById('father-name').value = data.father_name || '';
					document.getElementById('mother-name').value = data.mother_name || '';
					document.getElementById('home-address').value = data.home_address || '';
				} else {
					// If no data is returned, clear the form fields
					clearFormFields();
				}
			})
			.catch(error => {
				// Handle fetch error
				console.error('Error fetching data:', error);
				clearFormFields();
			});
		} else {
			// If no family code is entered, clear the form fields
			clearFormFields();
		}
	}

	// Helper function to set the selected value for a select element
	function setSelectValue(selectId, value)
	{
		var select = document.getElementById(selectId);
		if (select) {
			var found = false;
			for (var i = 0; i < select.options.length; i++) {
				if (select.options[i].value === value) {
					select.selectedIndex = i;
					found = true;
					break;
				}
			}
			// If no option matches, set the first one as a fallback
			if (!found) {
				select.selectedIndex = 0;
			}
		}
	}

	// Helper function to clear the form fields
	function clearFormFields()
	{
		var fields = [
			'religion', 'father-cell-no', 'mother-cell-no',
			'state', 'city', 'father-name', 'mother-name', 'home-address'
		];

		fields.forEach(function(fieldId) {
			var field = document.getElementById(fieldId);
			if (field) {
				if (field.tagName === "SELECT") {
					field.selectedIndex = 0;  // Reset the select dropdown
				} else {
					field.value = '';  // Reset input fields
				}
			}
		});
	}

</script>




<?php

$conn->close();
include 'footer.php';
?>
