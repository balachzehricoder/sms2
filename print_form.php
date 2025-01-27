<?php
include 'header.php';
include 'sidebar.php';
?>

<div class="content-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Print Students Data</h4>

                        <form action="generate_print.php" method="GET" target="_blank">
                            <div class="mb-3">
                                <label>Select Fields to Print:</label><br>
                                <input type="checkbox" name="fields[]" value="student_id" checked> Student ID<br>
                                <input type="checkbox" name="fields[]" value="family_code" checked> Family Code<br>
                                <input type="checkbox" name="fields[]" value="student_name" checked> Student Name<br>
                                <input type="checkbox" name="fields[]" value="gender"> Gender<br>
                                <input type="checkbox" name="fields[]" value="class_id"> Class ID<br>
                                <input type="checkbox" name="fields[]" value="section_id"> Section ID<br>
                                <input type="checkbox" name="fields[]" value="date_of_admission"> Date of Admission<br>
                                <input type="checkbox" name="fields[]" value="status"> Status<br>
                            </div>

                            <div class="mb-3">
                                <label>Sort By:</label><br>
                                <select name="sort_by" class="form-control">
                                    <option value="date_of_admission" selected>Date of Admission</option>
                                    <option value="class_id">Class</option>
                                    <option value="family_code">Family Code</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Generate Print</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>