<?php
include 'confiq.php'; // Include database configuration
include 'header.php';
include 'sidebar.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Struck-Off Students</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Struck-Off Students</h4>

                        <!-- Search Bar -->
                        <div class="mb-3">
                            <input type="text" id="searchStudent" class="form-control" placeholder="Search by Student Name">
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Student Name</th>
                                        <th>Family Code</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTable">
                                    <!-- Data will be loaded here via AJAX -->
                                    <tr>
                                        <td colspan="6" class="text-center">Loading...</td>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Load initial data
    function loadStudents(search = '') {
        $.ajax({
            url: 'fetch_struck_off_students.php',
            type: 'GET',
            data: {
                search: search
            },
            success: function(response) {
                $('#studentsTable').html(response);
            },
            error: function() {
                $('#studentsTable').html('<tr><td colspan="6" class="text-center">Failed to load data.</td></tr>');
            }
        });
    }

    // Load students on page load
    $(document).ready(function() {
        loadStudents();

        // Search functionality
        $('#searchStudent').on('input', function() {
            const search = $(this).val();
            loadStudents(search);
        });
    });
</script>

<?php include 'footer.php'; ?>