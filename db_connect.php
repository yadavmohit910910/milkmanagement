<?php
$insert = false;
$update = false;
$delete = false;
$host = 'localhost'; // or your database host
$db = 'milk_management'; // your database name
$user = 'root'; // your database username
$pass = ''; // your database password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>