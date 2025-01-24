<?php
include 'confiq.php'; // Replace with your database configuration file

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize inputs to prevent SQL injection
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);

    // Query to check if the admin exists
    $query = "SELECT * FROM admin WHERE ADMIN_NAME = ? AND ADMIN_PASSWORD = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Valid login, set session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index');
        exit();
    } else {
        // Invalid login
        echo "<script>alert('Invalid username or password');</script>";
        echo "<script>window.location.href = 'login';</script>";
    }
}
?>

<!DOCTYPE html>
<html class="h-100" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>hazara school managment system</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/images/favicon.png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link href="css/style.css" rel="stylesheet">
    
</head>
<style>.toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
}

.fa-eye, .fa-eye-slash {
    font-size: 20px;
}
</style>
<body class="h-100">
    
    <div class="login-form-bg h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100">
                <div class="col-xl-6">
                    <div class="form-input-content">
                        <div class="card login-form mb-0">
                            <div class="card-body pt-5">
                                <a class="text-center" href="index.html"> <h4>Sign In</h4></a>

                                <!-- Display error message -->
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>

                                <form class="mt-5 mb-5 login-input" method="post">
                                    <div class="form-group">
                                        <input type="text" name="username" class="form-control" placeholder="user name" required>
                                    </div>
                                    <div class="form-group mb-2" style="position: relative;">
    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
    <span class="toggle-password" onclick="togglePassword()">
        <i class="fa fa-eye" id="toggleIcon"></i>
    </span>
</div>

                                    <div class="form-group mb-2">
                                        <p class="text-right login-form__footer"><a href="forget-password.html" class="text-primary">Forget Password</a></p>
                                    </div>
                                    <input type="submit" value="Sign in" class="btn login-form__btn submit w-100"></input>
                                </form
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>function togglePassword() {
    var passwordField = document.getElementById("password");
    var toggleIcon = document.getElementById("toggleIcon");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
