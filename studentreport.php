<?php
include 'header.php';
include 'sidebar.php';
?>
<style>
	.button-row {
		display: flex;
		justify-content: space-between;
		gap: 12px;
		margin: 12px 0;
	}

	button {
		flex: 1;
		padding: 14px;
		font-size: 15px;
		color: #ffffff;
		background-color: #007bff;
		border: none;
		border-radius: 6px;
		cursor: pointer;
		transition: background-color 0.3s ease, transform 0.3s ease;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	}

	button:hover {
		background-color: #0056b3;
		transform: translateY(-2px);
	}

	button:nth-child(even) {
		background-color: #6c757d;
	}

	button:nth-child(even):hover {
		background-color: #495057;
	}

	@media (max-width: 600px) {
		.button-row {
			flex-direction: column;
		}
	}
</style>






<body>

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
			<div class="button-row">
				<button onclick="redirectToClasswise()">Classwise Students Reports</button>
				<button onclick="redirectToAllStudents()">View All Students</button>
			</div>

			<script>
				function redirectToClasswise() {
					window.location.href = 'classwise';
				}

				function redirectToAllStudents() {
					window.location.href = 'all_students';
				}

				function redirectToStruckOff() {
					window.location.href = 'struck_off';
				}

				function redirectToGenderWise() {
					window.location.href = 'genderwise';
				}
			</script>


			<div class="button-row">
				<button>Admissions Stats</button>
				<button>Locality Wise Reports</button>
			</div>
			<div class="button-row">
				<button onclick="redirectToAdmissionWise()">Datewise Admissions Report</button>
				<button onclick="redirectToGenderWise()">Gender Wise Report</button>
			</div>

			<script>
				function redirectToAdmissionWise() {
					window.location.href = 'datewise_admission'
				}
			</script>

			<div class="button-row">
				<button onclick="window.location.href='age_wise_report'">Age Wise Students Report</button>
				<button onclick="redirectToStruckOff()">Struck off/Old Students</button>
			</div>
			<div class="button-row">
				<button onclick="redirectToClasswise1()">Religion Wise Report</button>
				<button onclick="redirectToContacts()">Contacts Report</button>

				<script>
					function redirectToClasswise1() {
						window.location.href = 'relisionwise';
					}

					function redirectToContacts() {
						window.location.href = 'contacts_report';
					}
				</script>
			</div>
			<div class="button-row">
				<button onclick="window.location.href='generate_id_cards'">Generate ID Cards</button>



			</div>

		</div>
	</div>



	<?php include 'footer.php' ?>
</body>