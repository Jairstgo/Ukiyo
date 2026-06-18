<?php
error_reporting(0);
session_start();
include 'conexion.php';

header('Content-Type: application/json');

$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);

if (empty($usuario) || empty($password)) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Por favor llena todos los campos.'
    ]);
    exit();
}

$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = mysqli_prepare($conn, $sql);

// Si la tabla no existe o falla la preparación
if (!$stmt) {
    echo json_encode(['success' => false, 'mensaje' => 'Error interno del servidor.']);
    exit();
}

mysqli_stmt_bind_param($stmt, 's', $usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al consultar la base de datos.']);
    exit();
}

if ($fila = mysqli_fetch_assoc($resultado)) {
    if (password_verify($password, $fila['password'])) {
        $_SESSION['idUsuario'] = $fila['idUsuario'];
        $_SESSION['nombre'] = $fila['nombre'];
        $_SESSION['usuario'] = $fila['usuario'];
        $_SESSION['rol'] = $fila['rol'];

        echo json_encode([
            'success' => true,
            'rol' => $fila['rol'],
            'mensaje' => 'Bienvenido, ' . $fila['nombre']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Usuario o contrasena incorrectos.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Usuario o contrasena incorrectos.'
    ]);
}
?>