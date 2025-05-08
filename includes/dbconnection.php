<?php
$servername = "localhost";
$username = "root";
$password = "Ashoka1967@";
$dbname = "attendance";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
