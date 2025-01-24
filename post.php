<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Fetch all records
$query = "SELECT class_name, standard_monthly_fee, created_at FROM classes ORDER BY class_name ASC";
$result = $conn->query($query);
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Classes</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">All Classes</h4>
                            <button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-class">
                                <i class="fa fa-plus-circle"></i> Add New Class
                            </button>
                        </div>

                        <!-- Classes Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Standard Monthly Fee</th>
                                        <th>Created At</th>
                                        <th>Created At</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>" . htmlspecialchars($row['class_name']) . "</td>
                                                    <td>" . htmlspecialchars($row['standard_monthly_fee']) . "</td>
                                                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                                                  <td>
    <a href='editclass.php?class_id=<?= htmlspecialchars(urlencode($Row[class_id])) ?>'class='btn btn-success'>Edit</a>
</td>


                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>No classes found</td></tr>";
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

<!-- Add New Class Modal -->
<div class="modal fade" id="add-new-class">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Class</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="basic-form">
                    <form action="addclass.php" method="post">
                        <div class="form-group">
                            <label for="class-name">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class-name" name="class_name" placeholder="Enter class name..." required>
                        </div>
                        <div class="form-group">
                            <label for="standard-monthly-fee">Standard Monthly Fee <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="standard-monthly-fee" name="standard_monthly_fee" placeholder="Enter monthly fee..." required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Add Class</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';
?>