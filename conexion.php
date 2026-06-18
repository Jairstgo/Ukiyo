<?php
$servername = "thomas.proxy.rlwy.net";
$database = "railway";
$username = "root";
$password = "YfoIOwrYLPBLnJiaQHxBymABVYixgPhw";
$port = 40852;

$conn = mysqli_connect($servername, $username, $password, $database, $port);

if (!$conn) {
    die("Error de conexion: " . mysqli_connect_error());
}
?>
