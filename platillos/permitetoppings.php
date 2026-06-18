<?php
error_reporting(0);
session_start();
include '../conexion.php';
header('Content-Type: application/json');

$id = $_POST['id'];
$valor = $_POST['valor'];

$sql = "UPDATE subcategorias SET permite_toppings = ? WHERE idSubcategoria = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $valor, $id);
echo mysqli_stmt_execute($stmt) ? json_encode(['success' => true]) : json_encode(['success' => false, 'mensaje' => mysqli_error($conn)]);