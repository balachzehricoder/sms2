 <?php  
// $host = 'localhost';
// $username = 'hazaraac_sms';
// $password = 'ij2yI3b${*S_'; // No password for default XAMPP setup
// $database = 'hazaraac_jamber'; // Replace with your actual database name

// $conn = new mysqli($host, $username, $password, $database);

// if ($conn->connect_error) {
    // die("Connection failed: " . $conn->connect_error);
//}



$host = 'localhost';
$username = 'root';
$password = ''; // No password for default XAMPP setup
$database = 'school'; // Replace with your actual database name

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
