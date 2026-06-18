<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['idUsuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$exito = $_GET['exito'] ?? 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $precio = $_POST['precio'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if (empty($nombre) || $precio === '') {
        $error = 'Nombre y precio son obligatorios.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO toppings (nombre, precio, disponible) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sdi', $nombre, $precio, $disponible);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: toppings.php?exito=1');
            exit();
        } else {
            $error = 'Error al registrar el topping.';
        }
    }
}

$toppings = mysqli_query($conn, "SELECT * FROM toppings ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ukiyo | Toppings Extra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
        <a href="listar.php" class="btn-back"><i class="bi bi-arrow-left"></i> Platillos</a>
        <span class="section-title">Toppings extra</span>
    </div>
    <div class="divider-rojo"></div>

    <?php if ($exito == 1): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Topping registrado correctamente.</div>
    <?php elseif ($exito == 2): ?>
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle"></i> Eliminado correctamente.</div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card-ukiyo">
        <div class="card-header-ukiyo"><i class="bi bi-plus-circle"></i> Nuevo topping</div>
        <div class="card-body-ukiyo">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Nombre del topping</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Extra queso, Huevo frito..." required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Disponible</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="disponible" checked>
                            <label class="form-check-label" style="font-size:13px;">Activo</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-ukiyo"><i class="bi bi-plus-lg"></i> Registrar topping</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-ukiyo mt-4">
        <div class="card-header-ukiyo"><i class="bi bi-list-ul"></i> Toppings registrados</div>
        <div class="card-body-ukiyo">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaToppings">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Disponible</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($t = mysqli_fetch_assoc($toppings)): ?>
                        <tr>
                            <td><?php echo $t['idTopping']; ?></td>
                            <td><?php echo $t['nombre']; ?></td>
                            <td>$<?php echo number_format($t['precio'], 2); ?></td>
                            <td>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" <?php echo $t['disponible'] ? 'checked' : ''; ?> onchange="toggleDisponible(<?php echo $t['idTopping']; ?>, this)">
                                </div>
                            </td>
                            <td>
                                <button class="btn-accion btn-eliminar" onclick="eliminar(<?php echo $t['idTopping']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#tablaToppings').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }, pageLength: 10 });
});

function toggleDisponible(id, checkbox) {
    const valor = checkbox.checked ? 1 : 0;
    $.ajax({
        type: 'POST',
        url: 'toggleToppingDisponible.php',
        data: { id, valor },
        dataType: 'json',
        success: function(r) {
            if (!r.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje, confirmButtonColor: '#C0392B' });
                checkbox.checked = !checkbox.checked;
            }
        }
    });
}

function eliminar(id) {
    Swal.fire({
        title: 'Eliminar topping',
        text: 'Esta accion no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#C0392B',
        cancelButtonColor: '#555',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: 'eliminarTopping.php',
                data: { id },
                dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje, confirmButtonColor: '#C0392B' });
                    }
                }
            });
        }
    });
}
</script>
</body>
</html>