<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];

    if (empty($nombre) || empty($usuario) || empty($password)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        $check = mysqli_prepare($conn, "SELECT idUsuario FROM usuarios WHERE usuario = ?");
        mysqli_stmt_bind_param($check, 's', $usuario);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $mensaje = 'Ese nombre de usuario ya existe.';
            $tipo_mensaje = 'danger';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssss', $nombre, $usuario, $hash, $rol);

            if (mysqli_stmt_execute($stmt)) {
                header('Location: listar.php?exito=1');
                exit();
            } else {
                $mensaje = 'Error al registrar el empleado.';
                $tipo_mensaje = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Registrar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>

<nav class="navbar-ukiyo">
    <a class="navbar-brand-ukiyo" href="../index.php">
        <div class="brand-icon"><img src="../assets/logo.png" alt="Ukiyo" style="width:36px; height:36px; object-fit:contain;"></div>
        <div class="brand-text">
            <span class="brand-name">UKIYO</span>
            <span class="brand-sub">Restaurante Japonés</span>
        </div>
    </a>
    <div class="nav-right">
        <div class="usuario-badge">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?></div>
            <span class="d-none d-md-inline"><?php echo $_SESSION['nombre']; ?></span>
        </div>
        <a href="../cerrarSesion.php" class="btn-salir"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </div>
</nav>

<div class="inner-content">
    <div class="section-header">
        <a href="listar.php" class="btn-back"><i class="bi bi-arrow-left"></i> Empleados</a>
        <span class="section-title">Registrar empleado</span>
    </div>
    <div class="divider-rojo"></div>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> mb-3"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="card-ukiyo">
        <div class="card-header-ukiyo"><i class="bi bi-person-plus"></i> Nuevo empleado</div>
        <div class="card-body-ukiyo">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre y apellidos" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-control" placeholder="Nombre de usuario" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Contraseña segura" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="empleado">Empleado</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-ukiyo"><i class="bi bi-person-check"></i> Registrar empleado</button>
                        <a href="listar.php" class="btn-outline-ukiyo ms-2"><i class="bi bi-x"></i> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>