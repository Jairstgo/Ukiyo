<?php
// En producción (Render) usa variables de entorno; en local usa los valores por defecto
$servername = getenv('DB_HOST') ?: 'localhost';
$database   = getenv('DB_NAME') ?: 'Ukiyo';
$username   = getenv('DB_USER') ?: 'root';
$password   = getenv('DB_PASS') ?: 'Oncrack1234_';
$port       = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306;

$conn = mysqli_connect($servername, $username, $password, $database, $port);

if (!$conn) {
    die("Error de conexion: " . mysqli_connect_error());
}
?>