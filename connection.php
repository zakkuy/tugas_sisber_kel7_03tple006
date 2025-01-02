<?php 
$servername = "localhost";
$database = "nexlit";
$username = "root";
$password = "";

$connection = mysqli_connect($servername, $username, $password, $database);

if (!$connection) {
    die ("Connection Failed :" . mysqli_connect_error());
}
?>