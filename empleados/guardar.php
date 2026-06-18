<?php
error_reporting(0);
session_start();
include '../conexion.php';
header('Content-Type: application/json');
$nombre = trim($_POST['nombre']);
$usuario = trim($_POST['usuario']);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$rol = $_POST['rol'];
$check = mysqli_prepare($conn, "SELECT idUsuario FROM usuarios WHERE usuario = ?");
mysqli_stmt_bind_param($check, 's', $usuario);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) { echo json_encode(['success' => false, 'mensaje' => 'Ese nombre de usuario ya existe.']); exit(); }
$sql = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $usuario, $password, $rol);
echo mysqli_stmt_execute($stmt) ? json_encode(['success' => true]) : json_encode(['success' => false, 'mensaje' => mysqli_error($conn)]);