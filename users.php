<?php
include 'confiq.php';
include 'header.php';
include 'sidebar.php';

// Handle role filter
$role = isset($_GET['role']) ? $_GET['role'] : null;

// Fetch users based on selected role
if ($role) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = ? ORDER BY first_name ASC");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $stmt->close();
} else {
    $user_query = "SELECT * FROM users ORDER BY first_name ASC";
    $user_result = $conn->query($user_query);
}
?>

<div class="content-body">
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Users</a></li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">All Users</h4>
                            <button type="button" class="btn btn-rounded btn-success" data-toggle="modal" data-target="#add-new-user">
                                <i class="fa fa-plus-circle"></i> Add New User
                            </button>
                        </div>

                        <!-- Role Filter -->
                        <form method="get" action="">
                            <label for="role">Filter by Role:</label>
                            <select name="role" id="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo ($role == 'admin' ? 'selected' : ''); ?>>Admin</option>
                                <option value="user" <?php echo ($role == 'user' ? 'selected' : ''); ?>>User</option>
                                <!-- Add more roles as needed -->
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>

                        <!-- User Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered zero-configuration">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>CNIC</th>
                                        <th>Date of Birth</th>
                                        <th>Address</th>
                                        <th>Mobile Number</th>
                                        <th>Optional Mobile</th>
                                        <th>Sponsor ID</th>
                                        <th>Points</th>
                                        <th>Group Points</th>
                                        <th>Commission</th>
                                        <th>Created At</th>
                                        <th>Profile Picture</th>
                                        <th>ID Card Picture</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($user_result->num_rows > 0) {
                                    while ($user = $user_result->fetch_assoc()) {
                                        // Handle image URLs
                                        $profileImage = str_replace("admin/", "", $user['profile_pic']);
                                        $idCardImage = str_replace("admin/", "", $user['id_card_picture']);

                                        echo "<tr>
                                            <td>" . htmlspecialchars($user['id']) . "</td>
                                            <td>" . htmlspecialchars($user['first_name']) . "</td>
                                            <td>" . htmlspecialchars($user['last_name']) . "</td>
                                            <td>" . htmlspecialchars($user['email']) . "</td>
                                            <td>" . htmlspecialchars($user['cnic']) . "</td>
                                            <td>" . htmlspecialchars($user['dob']) . "</td>
                                            <td>" . htmlspecialchars($user['address']) . "</td>
                                            <td>" . htmlspecialchars($user['mobile_number']) . "</td>
                                            <td>" . htmlspecialchars($user['optional_mobile_number']) . "</td>
                                            <td>" . htmlspecialchars($user['sponsor_id']) . "</td>
                                            <td>" . htmlspecialchars($user['points']) . "</td>
                                            <td>" . htmlspecialchars($user['group_points']) . "</td>
                                            <td>" . htmlspecialchars($user['commission']) . "</td>
                                            <td>" . htmlspecialchars($user['created_at']) . "</td>
                                            <td><img src='" . htmlspecialchars($profileImage) . "' alt='Profile Pic' width='50' height='50'></td>
                                            <td><img src='" . htmlspecialchars($idCardImage) . "' alt='ID Card' width='50' height='50'></td>
                                           <td>
    <!-- Form for deleting a user -->
    <form method='get' action='delete_user.php' style='display:inline;' onsubmit='return confirm('Are you sure you want to delete this user?');'>
        <!-- Hidden input for user_id, which will be passed in the URL -->
        <input type='hidden' name='user_id' value='<?php echo htmlspecialchars($user[id]); ?>'>
        <!-- Submit button to delete the user -->
        <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
    </form>
</td>

                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='17'>No users found</td></tr>";
                                }
                                ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>CNIC</th>
                                        <th>Date of Birth</th>
                                        <th>Address</th>
                                        <th>Mobile Number</th>
                                        <th>Optional Mobile</th>
                                        <th>Sponsor ID</th>
                                        <th>Points</th>
                                        <th>Group Points</th>
                                        <th>Commission</th>
                                        <th>Created At</th>
                                        <th>Profile Picture</th>
                                        <th>ID Card Picture</th>
                                        <th>Actions</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'footer.php';
?>
