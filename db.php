<?php
$conn = mysqli_connect("localhost", "root", "", "apotek_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>