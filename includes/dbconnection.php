<?php
$servername = "localhost";
$username = "username";
$password = "your pwd";
$dbname = "DB name";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
