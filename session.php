<?php




error_reporting(0);
session_start();
        if (!isset($_SESSION['admin_username'])) {
    header('Location: login.php');
     exit();
 } 