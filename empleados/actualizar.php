<?php
error_reporting(0);
session_start();
include '../conexion.php';
header('Content-Type: application/json');
$id = $_POST['id'];
$nombre = trim($_POST['nombre']);
$usuario = trim($_POST['usuario']);
$rol = $_POST['rol'];
$password = trim($_POST['password']);
if (!empty($password)) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "UPDATE usuarios SET nombre=?, usuario=?, password=?, rol=? WHERE idUsuario=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $usuario, $hash, $rol, $id);
} else {
    $sql = "UPDATE usuarios SET nombre=?, usuario=?, rol=? WHERE idUsuario=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $usuario, $rol, $id);
}
echo mysqli_stmt_execute($stmt) ? json_encode(['success' => true]) : json_encode(['success' => false, 'mensaje' => mysqli_error($conn)]);