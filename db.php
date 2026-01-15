<?php
$servername = "localhost";
$username = "root"; // Default for XAMPP/WAMP
$password = "";     // Default is usually empty
$dbname = "volcano_booking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>