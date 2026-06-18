<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$ventas_hoy = 0;
$pedidos_hoy = 0;
$platillos_activos = 0;
$stock_bajo = 0;

$q1 = mysqli_query($conn, "SELECT COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) as total FROM pedidos p JOIN detalle_pedido dp ON p.idPedido = dp.idPedido WHERE DATE(p.fechaRegistro) = CURDATE()");
if ($r1 = mysqli_fetch_assoc($q1)) $ventas_hoy = $r1['total'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM pedidos WHERE DATE(fechaRegistro) = CURDATE()");
if ($r2 = mysqli_fetch_assoc($q2)) $pedidos_hoy = $r2['total'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM platillos WHERE disponible = 1");
if ($r3 = mysqli_fetch_assoc($q3)) $platillos_activos = $r3['total'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventario WHERE cantidad <= stock_minimo");
if ($r4 = mysqli_fetch_assoc($q4)) $stock_bajo = $r4['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<nav class="navbar-ukiyo">
    <a class="navbar-brand-ukiyo" href="index.php">
       <div class="brand-icon">
    <img src="assets/logo.png" alt="Ukiyo" style="width:34px; height:34px; object-fit:contain; border-radius:6px;">
</div>
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
        <a href="cerrarSesion.php" class="btn-salir"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </div>
</nav>

<div class="page-content">
    <div class="bienvenida-hero">
        <div class="bienvenida-saludo">Panel de administración</div>
        <div class="bienvenida-titulo">Bienvenido, <?php echo $_SESSION['nombre']; ?></div>
        <div class="bienvenida-subtitulo">Resumen del día — selecciona una opción para continuar</div>
        <div class="stats-row">
            <div class="stat-mini">
                <div class="stat-mini-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-mini-val">$<?php echo number_format($ventas_hoy, 2); ?></div>
                    <div class="stat-mini-lbl">Ventas hoy</div>
                </div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-icon"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-mini-val"><?php echo $pedidos_hoy; ?></div>
                    <div class="stat-mini-lbl">Pedidos hoy</div>
                </div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-icon"><i class="bi bi-egg-fried"></i></div>
                <div>
                    <div class="stat-mini-val"><?php echo $platillos_activos; ?></div>
                    <div class="stat-mini-lbl">Platillos activos</div>
                </div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-mini-val"><?php echo $stock_bajo; ?></div>
                    <div class="stat-mini-lbl">Stock bajo</div>
                </div>
            </div>
        </div>
    </div>

    <div class="opciones-grid">
        <div class="opcion-card" onclick="window.location.href='platillos/listar.php'">
            <div class="opcion-icon"><i class="bi bi-egg-fried"></i></div>
            <div class="opcion-titulo">Platillos y menú</div>
            <div class="opcion-desc">Registra, edita o desactiva platillos y bebidas del menú.</div>
            <div class="opcion-flecha"><i class="bi bi-arrow-right"></i></div>
        </div>
        <div class="opcion-card" onclick="window.location.href='inventario/listar.php'">
            <div class="opcion-icon"><i class="bi bi-box-seam"></i></div>
            <div class="opcion-titulo">Inventario</div>
            <div class="opcion-desc">Controla los ingredientes e insumos disponibles en el restaurante.</div>
            <div class="opcion-flecha"><i class="bi bi-arrow-right"></i></div>
        </div>
        <div class="opcion-card" onclick="window.location.href='empleados/listar.php'">
            <div class="opcion-icon"><i class="bi bi-people"></i></div>
            <div class="opcion-titulo">Empleados</div>
            <div class="opcion-desc">Registra y gestiona los usuarios con acceso al sistema.</div>
            <div class="opcion-flecha"><i class="bi bi-arrow-right"></i></div>
        </div>
        <div class="opcion-card" onclick="window.location.href='reportes/index.php'">
            <div class="opcion-icon"><i class="bi bi-bar-chart-line"></i></div>
            <div class="opcion-titulo">Reporte del día</div>
            <div class="opcion-desc">Consulta las ventas del día y exporta el reporte en PDF.</div>
            <div class="opcion-flecha"><i class="bi bi-arrow-right"></i></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>